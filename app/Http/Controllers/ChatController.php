<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Services\BaileysService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
    ) {}

    public function index(Request $request)
    {
        $userId = Auth::id();

        $contacts = WaContact::where('user_id', $userId)
            ->withCount(['messages as unread' => fn($q) => $q->where('direction', 'in')])
            ->orderByDesc(
                WaMessage::select('created_at')
                    ->whereColumn('contact_id', 'wa_contacts.id')
                    ->latest()
                    ->take(1)
            )
            ->get()
            ->map(function ($contact) {
                $lastMsg = WaMessage::where('contact_id', $contact->id)
                    ->latest()
                    ->first();
                $contact->last_message = $lastMsg?->message;
                $contact->last_time = $lastMsg?->created_at;
                $contact->last_direction = $lastMsg?->direction;
                return $contact;
            });

        $sessions = WaSession::where('user_id', $userId)
            ->where('status', 'connected')
            ->get();

        $activeContact = null;
        $messages = collect();

        if ($request->has('contact')) {
            $activeContact = WaContact::where('user_id', $userId)
                ->find($request->get('contact'));
            if ($activeContact) {
                $messages = WaMessage::where('contact_id', $activeContact->id)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        return view('chat.index', compact('contacts', 'sessions', 'activeContact', 'messages'));
    }

    public function conversation(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $messages = WaMessage::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get();

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        $displayPhone = $contact->display_phone;
        if (!$displayPhone && !str_contains($contact->phone, '@lid')) {
            $displayPhone = preg_replace('/@.*$/', '', $contact->phone);
        }
        $showName = $contact->name !== $contact->phone ? $contact->name : ($displayPhone ?: preg_replace('/@.*$/', '', $contact->phone));

        $autoreplies = WaAutoreply::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get()
            ->map(fn($a) => ['keyword' => $a->keyword, 'match_type' => $a->match_type]);

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $showName,
                'phone' => $contact->phone,
                'display_phone' => $displayPhone,
            ],
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'direction' => $m->direction,
                'message' => $m->message,
                'status' => $m->status,
                'time' => $m->created_at->format('H:i'),
                'date' => $m->created_at->format('d M Y'),
                'full_time' => $m->created_at->toIso8601String(),
            ]),
            'sessions' => $sessions->map(fn($s) => [
                'id' => $s->id,
                'session_id' => $s->session_id,
                'name' => $s->name,
            ]),
            'autoreplies' => $autoreplies,
        ]);
    }

    public function send(Request $request, WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $request->validate([
            'message' => 'required|string|max:5000',
            'session_id' => 'required|exists:wa_sessions,session_id',
        ]);

        $session = WaSession::where('user_id', Auth::id())
            ->where('session_id', $request->session_id)
            ->firstOrFail();

        if (!$session->server || $session->status !== 'connected') {
            return response()->json(['ok' => false, 'error' => 'Session tidak terhubung'], 422);
        }

        $result = $this->baileys->send(
            $session->server,
            $session->session_id,
            $contact->phone,
            $request->message
        );

        if ($result['ok'] ?? false) {
            $msg = WaMessage::create([
                'user_id' => Auth::id(),
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'message' => $request->message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);

            return response()->json([
                'ok' => true,
                'message' => [
                    'id' => $msg->id,
                    'direction' => 'out',
                    'message' => $msg->message,
                    'status' => $msg->status,
                    'time' => $msg->created_at->format('H:i'),
                ],
            ]);
        }

        return response()->json(['ok' => false, 'error' => $result['error'] ?? 'Gagal kirim'], 422);
    }

    public function pollMessages(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $since = request('since');
        $query = WaMessage::where('contact_id', $contact->id)->orderBy('created_at');
        if ($since) {
            $query->where('created_at', '>', $since);
        }
        $messages = $query->get();

        return response()->json([
            'messages' => $messages->map(fn($m) => [
                'id' => $m->id,
                'direction' => $m->direction,
                'message' => $m->message,
                'status' => $m->status,
                'time' => $m->created_at->format('H:i'),
                'date' => $m->created_at->format('d M Y'),
                'full_time' => $m->created_at->toIso8601String(),
            ]),
            'latest_time' => $messages->last()?->created_at->toIso8601String(),
        ]);
    }

    public function pollContacts()
    {
        $userId = Auth::id();

        $contacts = WaContact::where('user_id', $userId)
            ->get()
            ->map(function ($contact) {
                $lastMsg = WaMessage::where('contact_id', $contact->id)
                    ->latest()
                    ->first();
                $displayPhone = $contact->display_phone;
                if (!$displayPhone && !str_contains($contact->phone, '@lid')) {
                    $displayPhone = preg_replace('/@.*$/', '', $contact->phone);
                }
                $isLid = str_contains($contact->phone, '@lid');
                $showName = $contact->name !== $contact->phone ? $contact->name : ($displayPhone ?: preg_replace('/@.*$/', '', $contact->phone));
                return [
                    'id' => $contact->id,
                    'name' => $showName,
                    'phone' => $contact->phone,
                    'display_phone' => $displayPhone,
                    'is_lid' => $isLid,
                    'last_message' => $lastMsg?->message,
                    'last_time' => $lastMsg?->created_at?->format('H:i'),
                    'last_direction' => $lastMsg?->direction,
                ];
            })
            ->sortByDesc('last_time')
            ->values();

        return response()->json(['contacts' => $contacts]);
    }

    public function updateContact(Request $request, WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'display_phone' => 'nullable|string|max:30',
        ]);

        $update = [];
        if ($request->has('name')) {
            $update['name'] = $request->name;
        }
        if ($request->has('display_phone')) {
            $update['display_phone'] = $request->display_phone;
        }
        if ($update) {
            $contact->update($update);
        }

        $displayPhone = $contact->display_phone;
        if (!$displayPhone && !str_contains($contact->phone, '@lid')) {
            $displayPhone = preg_replace('/@.*$/', '', $contact->phone);
        }

        return response()->json([
            'ok' => true,
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name !== $contact->phone ? $contact->name : ($displayPhone ?: $contact->name),
                'phone' => $contact->phone,
                'display_phone' => $displayPhone,
            ],
        ]);
    }
}
