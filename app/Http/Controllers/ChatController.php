<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaInstagramAccount;
use App\Models\WaMessage;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Services\BaileysService;
use App\Services\InstagramService;
use App\Services\MetaApiService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected InstagramService $instagram,
        protected TelegramService $telegram,
        protected MetaApiService $metaApi,
    ) {}

    protected function detectChannel(WaContact $contact): string
    {
        if (str_starts_with($contact->phone, 'ig:')) {
            return 'instagram';
        }
        if (str_starts_with($contact->phone, 'tg:')) {
            return 'telegram';
        }
        return 'baileys';
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        $sessions = WaSession::where('user_id', $userId)
            ->where('status', 'connected')
            ->get();

        $connectedSessionIds = $sessions->pluck('id')->toArray();

        $contacts = WaContact::where('user_id', $userId)
            ->withCount(['messages as unread' => fn($q) => $q->where('direction', 'in')])
            ->where(function ($q) use ($connectedSessionIds) {
                $q->whereHas('messages', function ($q) use ($connectedSessionIds) {
                    $q->whereIn('session_id', $connectedSessionIds);
                })->orWhere(function ($q) {
                    $q->where('phone', 'like', 'ig:%')
                      ->orWhere('phone', 'like', 'tg:%');
                });
            })
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
                $contact->last_session_id = $lastMsg?->session_id;
                $contact->channel = $this->detectChannel($contact);
                return $contact;
            });

        $activeContact = null;
        $messages = collect();

        if ($request->has('contact')) {
            $activeContact = WaContact::where('user_id', $userId)
                ->find($request->get('contact'));
            if ($activeContact) {
                $activeContact->channel = $this->detectChannel($activeContact);
                $messages = WaMessage::where('contact_id', $activeContact->id)
                    ->orderBy('created_at')
                    ->get();
            }
        }

        $instagramAccounts = WaInstagramAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $telegramAccounts = WaTelegramAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $metaAccounts = WaMetaAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        return view('chat.index', compact(
            'contacts', 'sessions', 'activeContact', 'messages',
            'instagramAccounts', 'telegramAccounts', 'metaAccounts'
        ));
    }

    public function conversation(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $channel = $this->detectChannel($contact);

        $messages = WaMessage::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get();

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        $displayPhone = $contact->display_phone;
        if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:')) {
            $displayPhone = preg_replace('/@.*$/', '', $contact->phone);
        }
        $showName = $contact->name !== $contact->phone ? $contact->name : ($displayPhone ?: preg_replace('/@.*$/', '', $contact->phone));

        $autoreplies = WaAutoreply::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get()
            ->map(fn($a) => ['keyword' => $a->keyword, 'match_type' => $a->match_type]);

        $userId = Auth::id();
        $instagramAccounts = WaInstagramAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $telegramAccounts = WaTelegramAccount::where('user_id', $userId)
            ->where('is_active', true)->get();

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $showName,
                'phone' => $contact->phone,
                'display_phone' => $displayPhone,
                'channel' => $channel,
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
                'channel' => $s->channel ?? 'whatsapp',
                'meta_account_id' => $s->meta_account_id,
            ]),
            'autoreplies' => $autoreplies,
            'accounts' => [
                'instagram' => $instagramAccounts->map(fn($a) => [
                    'id' => 'ig_'.$a->id, 'name' => $a->name, 'instagram_id' => $a->instagram_id,
                ])->values(),
                'telegram' => $telegramAccounts->map(fn($a) => [
                    'id' => 'tg_'.$a->id, 'name' => $a->name, 'bot_username' => $a->bot_username,
                ])->values(),
            ],
        ]);
    }

    public function send(Request $request, WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $channel = $this->detectChannel($contact);
        $userId = Auth::id();

        $isWhatsApp = !in_array($channel, ['instagram', 'telegram']);
        $rules = ['message' => 'required|string|max:5000'];
        if ($isWhatsApp) {
            $rules['session_id'] = 'required|exists:wa_sessions,session_id';
        }
        $request->validate($rules);

        $result = null;
        $dbSessionId = null;
        $messageType = $channel;

        switch ($channel) {
            case 'instagram':
                $igAccount = WaInstagramAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$igAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun Instagram tidak tersedia'], 422);
                }
                $contactIgId = str_replace('ig:', '', $contact->phone);
                $result = $this->instagram->sendDM(
                    $igAccount->instagram_id,
                    $igAccount->access_token,
                    $request->message,
                    $contactIgId
                );
                break;

            case 'telegram':
                $tgAccount = WaTelegramAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$tgAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun Telegram tidak tersedia'], 422);
                }
                $chatId = str_replace('tg:', '', $contact->phone);
                $result = $this->telegram->sendMessage($tgAccount, $chatId, $request->message);
                break;

            default:
                $session = WaSession::where('user_id', $userId)
                    ->where('session_id', $request->session_id)
                    ->firstOrFail();
                $dbSessionId = $session->id;

                if ($session->meta_account_id) {
                    $metaAccount = WaMetaAccount::findOrFail($session->meta_account_id);
                    if (!$metaAccount->is_active) {
                        return response()->json(['ok' => false, 'error' => 'Akun Meta tidak aktif'], 422);
                    }
                    $to = preg_replace('/@.*$/', '', $contact->phone);
                    $result = $this->metaApi->sendText($metaAccount, $to, $request->message);
                    $messageType = 'meta';
                } else {
                    if (!$session->server || $session->status !== 'connected') {
                        return response()->json(['ok' => false, 'error' => 'Session tidak terhubung'], 422);
                    }
                    $result = $this->baileys->send(
                        $session->server,
                        $session->session_id,
                        $contact->phone,
                        $request->message
                    );
                    $messageType = 'whatsapp';
                }
                break;
        }

        $isOk = match ($channel) {
            'instagram' => empty($result['error']),
            'telegram' => $result['ok'] ?? false,
            default => ($result['ok'] ?? false) || empty($result['error']),
        };

        if ($isOk) {
            $msg = WaMessage::create([
                'user_id' => $userId,
                'session_id' => $dbSessionId,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => $messageType,
                'channel' => $messageType,
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

        $connectedSessionIds = WaSession::where('user_id', $userId)
            ->where('status', 'connected')
            ->pluck('id')
            ->toArray();

        $contacts = WaContact::where('user_id', $userId)
            ->where(function ($q) use ($connectedSessionIds) {
                $q->whereHas('messages', function ($q) use ($connectedSessionIds) {
                    $q->whereIn('session_id', $connectedSessionIds);
                })->orWhere(function ($q) {
                    $q->where('phone', 'like', 'ig:%')
                      ->orWhere('phone', 'like', 'tg:%');
                });
            })
            ->get()
            ->map(function ($contact) {
                $lastMsg = WaMessage::where('contact_id', $contact->id)
                    ->latest()
                    ->first();
                $displayPhone = $contact->display_phone;
                if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:')) {
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
                    'last_session_id' => $lastMsg?->session_id,
                    'channel' => $this->detectChannel($contact),
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
        if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:')) {
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

    public function suggestMerge(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $userId = Auth::id();
        $name = $contact->name;
        $phone = $contact->phone;

        $suggestions = WaContact::where('user_id', $userId)
            ->where('id', '!=', $contact->id)
            ->where('name', 'like', "%{$name}%")
            ->limit(10)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'phone' => $c->phone,
                    'display_phone' => $c->display_phone,
                    'channel' => $this->detectChannel($c),
                ];
            });

        $currentChannel = $this->detectChannel($contact);

        $crossChannel = $suggestions->filter(fn($s) => $s['channel'] !== $currentChannel)->values();

        return response()->json([
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'channel' => $currentChannel,
            ],
            'suggestions' => $suggestions,
            'cross_channel' => $crossChannel,
        ]);
    }
}
