<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Models\WaMessageTemplate;
use App\Services\BaileysService;
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

        return view('messages.send', compact('sessions', 'contacts', 'templates'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|exists:wa_sessions,id',
            'phone' => 'required_without:contact_id|string|max:30',
            'contact_id' => 'nullable|exists:wa_contacts,id',
            'message' => 'required|string|max:5000',
        ]);

        $session = WaSession::where('user_id', Auth::id())->findOrFail($data['session_id']);

        if (!$session->server || $session->status !== 'connected') {
            return back()->with('error', 'Sesi tidak terhubung.');
        }

        $phone = $data['phone'] ?? null;
        if ($data['contact_id'] ?? null) {
            $contact = WaContact::find($data['contact_id']);
            $phone = $contact->phone;
        } else {
            $phone = preg_replace('/[^0-9]/', '', $phone);
        }

        $baileys = app(BaileysService::class);
        $result = $baileys->send($session->server, $session->session_id, $phone, $data['message']);

        $contact = WaContact::firstOrCreate(
            ['user_id' => Auth::id(), 'phone' => $phone],
            ['name' => $phone]
        );

        WaMessage::create([
            'user_id' => Auth::id(),
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'message' => $data['message'],
            'phone' => $phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        if ($result['ok'] ?? false) {
            return redirect()->route('messages.sent')->with('success', 'Pesan terkirim.');
        }

        return back()->with('error', 'Gagal mengirim: ' . ($result['error'] ?? 'unknown'));
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
            return back()->with('error', 'Sesi tidak terhubung.');
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
            return back()->with('success', 'Pesan dikirim ulang.');
        }

        return back()->with('error', 'Gagal mengirim ulang: ' . ($result['error'] ?? 'unknown'));
    }

    public function destroy(WaMessage $message)
    {
        abort_if($message->user_id !== Auth::id(), 403);
        $message->delete();
        return back()->with('success', 'Pesan dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        WaMessage::where('user_id', Auth::id())->whereIn('id', $request->ids)->delete();
        return back()->with('success', count($request->ids) . ' pesan dihapus.');
    }
}
