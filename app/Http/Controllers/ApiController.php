<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaMetaAccount;
use App\Models\WaInstagramAccount;
use App\Models\WaTelegramAccount;
use App\Models\WaSession;
use App\Models\WaAutoreply;
use App\Models\WaCampaign;
use App\Services\BaileysService;
use App\Services\InstagramService;
use App\Services\TelegramService;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ApiController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected InstagramService $instagram,
        protected TelegramService $telegram,
        protected MetaApiService $metaApi,
    ) {}

    public function send(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $session = WaSession::where('user_id', Auth::id())
            ->where('session_id', $validated['session_id'])
            ->where('status', 'connected')
            ->first();

        if (!$session) {
            return response()->json(['ok' => false, 'error' => 'Session not found or not connected'], 404);
        }

        $result = $this->baileys->send(
            $session->server,
            $session->session_id,
            $validated['phone'],
            $validated['message']
        );

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => Auth::id(),
                'session_id' => $session->id,
                'direction' => 'out',
                'channel' => 'whatsapp',
                'message' => $validated['message'],
                'phone' => $validated['phone'],
                'status' => 'sent',
            ]);
        }

        return response()->json($result);
    }

    public function sendBulk(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'recipients' => 'required|array|min:1',
            'recipients.*' => 'string',
            'message' => 'required|string',
        ]);

        $session = WaSession::where('user_id', Auth::id())
            ->where('session_id', $validated['session_id'])
            ->where('status', 'connected')
            ->first();

        if (!$session || !$session->server) {
            return response()->json(['ok' => false, 'error' => 'Session not connected'], 404);
        }

        $result = $this->baileys->sendBulk(
            $session->server,
            $session->session_id,
            $validated['recipients'],
            $validated['message']
        );

        return response()->json($result);
    }

    public function sessions()
    {
        $sessions = WaSession::where('user_id', Auth::id())->get()->map(fn($s) => [
            'id' => $s->session_id,
            'name' => $s->name,
            'phone' => $s->phone,
            'status' => $s->status,
        ]);

        return response()->json(['data' => $sessions]);
    }

    public function sessionStatus(string $sessionId)
    {
        $session = WaSession::where('user_id', Auth::id())
            ->where('session_id', $sessionId)
            ->first();

        if (!$session || !$session->server) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $status = $this->baileys->getStatus($session->server, $session->session_id);
        return response()->json($status);
    }

    public function messages(Request $request)
    {
        $messages = WaMessage::where('user_id', Auth::id())
            ->when($request->session_id, fn($q, $sid) =>
                $q->whereHas('session', fn($s) => $s->where('session_id', $sid))
            )
            ->when($request->direction, fn($q, $d) => $q->where('direction', $d))
            ->latest()
            ->paginate($request->get('limit', 25));

        return response()->json($messages);
    }

    public function contacts(Request $request)
    {
        $contacts = WaContact::where('user_id', Auth::id())
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"))
            )
            ->latest()
            ->paginate($request->get('limit', 25));

        return response()->json($contacts);
    }

    public function campaigns(Request $request)
    {
        $campaigns = WaCampaign::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->paginate($request->get('limit', 10));

        return response()->json($campaigns);
    }

    public function autoreplyRules()
    {
        $rules = WaAutoreply::where('user_id', Auth::id())
            ->with('session')
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'keyword' => $r->keyword,
                'reply_message' => $r->reply_message,
                'match_type' => $r->match_type,
                'is_active' => $r->is_active,
                'session_id' => $r->session?->session_id,
            ]);

        return response()->json(['data' => $rules]);
    }

    public function profile()
    {
        $user = Auth::user()->load('plan');
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'plan' => $user->plan?->name ?? 'Free',
            'limits' => $user->plan ? [
                'sessions' => $user->plan->max_sessions,
                'contacts' => $user->plan->max_contacts,
                'autoreplies' => $user->plan->max_autoreplies,
            ] : null,
            'created_at' => $user->created_at,
        ]);
    }

    public function metaSend(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|string',
            'phone' => 'required|string',
            'message' => 'required|string|max:5000',
        ]);

        $account = WaMetaAccount::where('user_id', Auth::id())
            ->where('phone_number_id', $validated['account_id'])
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return response()->json(['ok' => false, 'error' => 'Meta account not found or inactive'], 404);
        }

        $result = $this->metaApi->sendText($account, $validated['phone'], $validated['message']);

        if (!empty($result['error'])) {
            return response()->json(['ok' => false, 'error' => $result['error']], 422);
        }

        $session = WaSession::where('meta_account_id', $account->id)->first();

        WaMessage::create([
            'user_id' => Auth::id(),
            'session_id' => $session?->id,
            'direction' => 'out',
            'type' => 'text',
            'channel' => 'meta',
            'message' => $validated['message'],
            'phone' => $validated['phone'],
            'status' => 'sent',
        ]);

        return response()->json(['ok' => true]);
    }

    public function instagramSend(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|string',
            'message' => 'required|string|max:5000',
        ]);

        $account = WaInstagramAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return response()->json(['ok' => false, 'error' => 'No active Instagram account'], 404);
        }

        $result = $this->instagram->sendDM(
            $account->instagram_id,
            $account->access_token,
            $validated['message'],
            $validated['recipient_id']
        );

        if (!empty($result['error'])) {
            return response()->json(['ok' => false, 'error' => $result['error']], 422);
        }

        $contact = WaContact::firstOrCreate(
            ['user_id' => Auth::id(), 'phone' => 'ig:' . $validated['recipient_id']],
            ['name' => 'IG: ' . $validated['recipient_id'], 'display_phone' => 'IG DM']
        );

        WaMessage::create([
            'user_id' => Auth::id(),
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'instagram',
            'channel' => 'instagram',
            'message' => $validated['message'],
            'phone' => 'ig:' . $validated['recipient_id'],
            'status' => 'sent',
        ]);

        return response()->json(['ok' => true]);
    }

    public function telegramSend(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|string',
            'message' => 'required|string|max:5000',
        ]);

        $account = WaTelegramAccount::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if (!$account) {
            return response()->json(['ok' => false, 'error' => 'No active Telegram account'], 404);
        }

        $result = $this->telegram->sendMessage($account, $validated['chat_id'], $validated['message']);

        if (!($result['ok'] ?? false)) {
            return response()->json(['ok' => false, 'error' => $result['description'] ?? 'Failed'], 422);
        }

        $senderId = 'tg:' . $validated['chat_id'];
        $contact = WaContact::firstOrCreate(
            ['user_id' => Auth::id(), 'phone' => $senderId],
            ['name' => 'TG: ' . $validated['chat_id'], 'display_phone' => $senderId]
        );

        WaMessage::create([
            'user_id' => Auth::id(),
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'telegram',
            'channel' => 'telegram',
            'message' => $validated['message'],
            'phone' => $senderId,
            'status' => 'sent',
        ]);

        return response()->json(['ok' => true]);
    }

    public function channels()
    {
        $userId = Auth::id();

        $baileys = WaSession::where('user_id', $userId)->get()->map(fn($s) => [
            'type' => 'whatsapp',
            'id' => $s->session_id,
            'name' => $s->name,
            'status' => $s->status,
            'phone' => $s->phone,
        ]);

        $meta = WaMetaAccount::where('user_id', $userId)->get()->map(fn($a) => [
            'type' => 'meta',
            'id' => $a->phone_number_id,
            'name' => $a->name,
            'status' => $a->is_active ? 'connected' : 'disconnected',
            'phone' => $a->phone_number,
        ]);

        $instagram = WaInstagramAccount::where('user_id', $userId)->get()->map(fn($a) => [
            'type' => 'instagram',
            'id' => $a->instagram_id,
            'name' => $a->name,
            'status' => $a->is_active ? 'connected' : 'disconnected',
        ]);

        $telegram = WaTelegramAccount::where('user_id', $userId)->get()->map(fn($a) => [
            'type' => 'telegram',
            'id' => $a->bot_id,
            'name' => $a->name,
            'status' => $a->is_active ? 'connected' : 'disconnected',
            'username' => $a->bot_username,
        ]);

        return response()->json([
            'data' => $baileys->concat($meta)->concat($instagram)->concat($telegram)->values(),
        ]);
    }

    public function webhookReceive(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|string',
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $session = WaSession::where('session_id', $data['session_id'])->first();
        if (!$session) {
            return response()->json(['ok' => false, 'reason' => 'session_not_found']);
        }

        if ($session->status !== 'connected') {
            $session->update([
                'status' => 'connected',
                'last_active_at' => now(),
            ]);
        }

        $normalizedPhone = preg_replace('/@.*$/', '', $data['phone']);

        $contact = WaContact::firstOrCreate(
            ['user_id' => $session->user_id, 'phone' => $data['phone']],
            ['name' => $normalizedPhone]
        );

        WaMessage::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'channel' => 'whatsapp',
            'message' => $data['message'],
            'phone' => $normalizedPhone,
            'status' => 'delivered',
        ]);

        $autoReply = WaAutoreply::where('user_id', $session->user_id)
            ->where('is_active', true)
            ->where(fn($q) => $q->where('session_id', $session->id)->orWhereNull('session_id'))
            ->get()
            ->first(fn($rule) => $rule->matches($data['message']));

        if ($autoReply && $session->server) {
            $result = $this->baileys->send(
                $session->server, $session->session_id,
                $data['phone'], $autoReply->reply_message
            );

            if ($result['ok'] ?? false) {
                WaMessage::create([
                    'user_id' => $session->user_id,
                    'session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'channel' => 'whatsapp',
                    'message' => $autoReply->reply_message,
                    'phone' => $normalizedPhone,
                    'status' => 'sent',
                ]);
            } else {
                Log::warning("API auto-reply failed to send", [
                    'keyword' => $autoReply->keyword,
                    'phone' => $data['phone'],
                    'error' => $result['error'] ?? 'unknown',
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }
}
