<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Models\WaMessageTemplate;
use App\Models\WaMetaAccount;
use App\Models\WaTelegramAccount;
use App\Services\BaileysService;
use App\Services\MetaApiService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function sendForm()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();
        $contacts = WaContact::where('user_id', Auth::id())->get();
        $templates = WaMessageTemplate::where('user_id', Auth::id())->get();
        $metaAccounts = WaMetaAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();
        $telegramAccounts = WaTelegramAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('messages.send', compact('sessions', 'contacts', 'templates', 'metaAccounts', 'telegramAccounts'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'channel' => 'required|in:whatsapp,meta,telegram',
            'session_id' => 'required_if:channel,whatsapp|nullable|exists:wa_sessions,id',
            'meta_account_id' => 'required_if:channel,meta|nullable|exists:wa_meta_accounts,id',
            'telegram_account_id' => 'required_if:channel,telegram|nullable|exists:wa_telegram_accounts,id',
            'phone' => 'required_without:contact_id|string|max:30',
            'contact_id' => 'nullable|exists:wa_contacts,id',
            'message' => 'required|string|max:5000',
        ]);

        $phone = $data['phone'] ?? null;
        if ($data['contact_id'] ?? null) {
            $contact = WaContact::find($data['contact_id']);
            $phone = $contact->phone;
        } else {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        $userId = Auth::id();
        $channel = $data['channel'];
        $dbSessionId = null;
        $result = null;

        switch ($channel) {
            case 'meta':
                $metaAccount = WaMetaAccount::where('user_id', $userId)->findOrFail($data['meta_account_id']);
                if (!$metaAccount->is_active) {
                    return back()->with('error', 'Akun Meta tidak aktif');
                }
                $to = preg_replace('/[^0-9]/', '', $phone);
                $result = app(MetaApiService::class)->sendText($metaAccount, $to, $data['message']);
                break;

            case 'telegram':
                $tgAccount = WaTelegramAccount::where('user_id', $userId)->findOrFail($data['telegram_account_id']);
                if (!$tgAccount->is_active) {
                    return back()->with('error', 'Akun Telegram tidak aktif');
                }
                $chatId = preg_replace('/[^0-9]/', '', $phone);
                $result = app(TelegramService::class)->sendMessage($tgAccount, $chatId, $data['message']);
                break;

            default:
                $session = WaSession::where('user_id', $userId)->findOrFail($data['session_id']);
                if (!$session->server || $session->status !== 'connected') {
                    return back()->with('error', __('messages.error.session_not_connected'));
                }
                $dbSessionId = $session->id;
                $baileys = app(BaileysService::class);
                $result = $baileys->send($session->server, $session->session_id, $phone, $data['message']);
                break;
        }

        $isOk = match ($channel) {
            'meta' => !isset($result['error']),
            'telegram' => $result['ok'] ?? false,
            default => $result['ok'] ?? false,
        };

        $contact = WaContact::firstOrCreate(
            ['user_id' => $userId, 'phone' => $phone],
            ['name' => $phone]
        );

        WaMessage::create([
            'user_id' => $userId,
            'session_id' => $dbSessionId,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'message' => $data['message'],
            'phone' => $phone,
            'channel' => $channel,
            'status' => $isOk ? 'sent' : 'failed',
        ]);

        if ($isOk) {
            return redirect()->route('messages.sent')->with('success', __('messages.success.message_sent'));
        }

        return back()->with('error', __('messages.error.send_failed', ['error' => ($result['error'] ?? 'unknown')]));
    }
    public function sent(Request $request)
    {
        $messages = WaMessage::where('user_id', Auth::id())
            ->where('direction', 'out')
            ->with('session', 'contact')
            ->latest()
            ->paginate(30);

        $sessions = WaSession::where('user_id', Auth::id())->get();

        return view('messages.sent', compact('messages', 'sessions'));
    }

    public function received(Request $request)
    {
        $messages = WaMessage::where('user_id', Auth::id())
            ->where('direction', 'in')
            ->with('session', 'contact')
            ->latest()
            ->paginate(30);

        $sessions = WaSession::where('user_id', Auth::id())->get();

        return view('messages.received', compact('messages', 'sessions'));
    }

    public function queue(Request $request)
    {
        $messages = WaMessage::where('user_id', Auth::id())
            ->where('direction', 'out')
            ->whereIn('status', ['pending', 'queued', 'sending'])
            ->with('session', 'contact')
            ->latest()
            ->paginate(30);

        return view('messages.queue', compact('messages'));
    }

    public function resend(WaMessage $message)
    {
        abort_if($message->user_id !== Auth::id(), 403);

        $session = $message->session;
        if (!$session || !$session->server || $session->status !== 'connected') {
            return back()->with('error', __('messages.error.session_not_connected'));
        }

        $baileys = app(\App\Services\BaileysService::class);
        $result = $baileys->send($session->server, $session->session_id, $message->phone, $message->message);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => Auth::id(),
                'session_id' => $session->id,
                'contact_id' => $message->contact_id,
                'direction' => 'out',
                'message' => $message->message,
                'phone' => $message->phone,
                'status' => 'sent',
            ]);
            return back()->with('success', __('messages.success.message_resent'));
        }

        return back()->with('error', __('messages.error.resend_failed', ['error' => ($result['error'] ?? 'unknown')]));
    }

    public function destroy(WaMessage $message)
    {
        abort_if($message->user_id !== Auth::id(), 403);
        $message->delete();
        return back()->with('success', __('messages.success.message_deleted'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        WaMessage::where('user_id', Auth::id())->whereIn('id', $request->ids)->delete();
        return back()->with('success', __('messages.success.messages_deleted_count', ['count' => count($request->ids)]));
    }

    public function search(Request $request)
    {
        $q = $request->get('q');
        $direction = $request->get('direction', 'in');

        $messages = WaMessage::where('user_id', Auth::id())
            ->where('direction', $direction)
            ->with('session', 'contact')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('message', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhereHas('contact', function ($c) use ($q) {
                          $c->where('name', 'like', "%{$q}%");
                      });
                });
            })
            ->latest()
            ->paginate(30)
            ->appends(['q' => $request->get('q'), 'direction' => $direction]);

        $sessions = WaSession::where('user_id', Auth::id())->get();

        $view = $direction === 'out' ? 'messages.sent' : 'messages.received';

        return view($view, compact('messages', 'sessions'));
    }
}
