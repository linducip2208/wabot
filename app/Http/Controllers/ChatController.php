<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaDiscordAccount;
use App\Models\WaGbmAccount;
use App\Models\WaFacebookAccount;
use App\Models\WaInstagramAccount;
use App\Models\WaMessage;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Services\BaileysService;
use App\Services\ChannelRegistry;
use App\Services\DiscordService;
use App\Services\GbmService;
use App\Services\FacebookService;
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
        protected DiscordService $discord,
        protected GbmService $gbm,
        protected FacebookService $facebook,
    ) {}

    protected function detectChannel(WaContact $contact): string
    {
        return ChannelRegistry::detectChannel($contact);
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
                })->orWhere(function ($q) use ($connectedSessionIds) {
                    foreach (ChannelRegistry::channelPhonePatterns() as $pattern) {
                        $q->orWhere('phone', 'like', $pattern);
                    }
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
                $contact->channel = ChannelRegistry::getByPhone($contact->phone);
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
        $gbmAccounts = WaGbmAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $discordAccounts = WaDiscordAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $facebookAccounts = WaFacebookAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $tiktokAccounts = \App\Models\WaTiktokAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $lineAccounts = \App\Models\WaLineAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();
        $twitterAccounts = \App\Models\WaTwitterAccount::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $socketServerUrl = null;
        $socketApiKey = null;
        $firstSession = $sessions->first();
        if ($firstSession && $firstSession->server) {
            $socketServerUrl = $firstSession->server->baseUrl();
            $socketApiKey = $firstSession->server->api_key;
        }

        $templates = \App\Models\WaMessageTemplate::where('user_id', $userId)->orderBy('name')->get();

        return view('chat.index', compact(
            'contacts', 'sessions', 'activeContact', 'messages',
            'instagramAccounts', 'telegramAccounts', 'metaAccounts',
            'gbmAccounts', 'discordAccounts', 'facebookAccounts',
            'tiktokAccounts', 'lineAccounts', 'twitterAccounts',
            'socketServerUrl', 'socketApiKey', 'templates'
        ));
    }

    public function conversation(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $channel = $this->detectChannel($contact);

        $messages = WaMessage::where('contact_id', $contact->id)
            ->orderBy('created_at')
            ->get();

        if (in_array($channel, ['instagram', 'telegram', 'facebook', 'gbm', 'discord', 'tiktok', 'line', 'twitter'])) {
            WaMessage::where('contact_id', $contact->id)
                ->where('direction', 'in')
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'status' => 'read']);
        }

        if ($channel === 'baileys') {
            $session = WaSession::where('user_id', Auth::id())
                ->where('status', 'connected')
                ->first();
            if ($session && $session->meta_account_id) {
                $metaAccount = WaMetaAccount::find($session->meta_account_id);
                if ($metaAccount) {
                    $unreadMetaMessages = WaMessage::where('contact_id', $contact->id)
                        ->where('direction', 'in')
                        ->where('channel', 'meta')
                        ->whereNull('read_at')
                        ->get();
                    foreach ($unreadMetaMessages as $msg) {
                        if ($msg->external_id) {
                            $this->metaApi->markAsRead($metaAccount, $msg->external_id);
                        }
                        $msg->update(['read_at' => now(), 'status' => 'read']);
                    }
                }
            }
        }

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        $displayPhone = $contact->display_phone;
        if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:') && !str_starts_with($contact->phone, 'gbm:') && !str_starts_with($contact->phone, 'dc:')) {
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
        $facebookAccounts = WaFacebookAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $gbmAccounts = WaGbmAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $discordAccounts = WaDiscordAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $tiktokAccounts = \App\Models\WaTiktokAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $lineAccounts = \App\Models\WaLineAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        $twitterAccounts = \App\Models\WaTwitterAccount::where('user_id', $userId)
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
                'facebook' => $facebookAccounts->map(fn($a) => [
                    'id' => 'fb_'.$a->id, 'name' => $a->name, 'page_id' => $a->page_id,
                ])->values(),
                'gbm' => $gbmAccounts->map(fn($a) => [
                    'id' => 'gbm_'.$a->id, 'name' => $a->name, 'brand_id' => $a->brand_id,
                ])->values(),
                'discord' => $discordAccounts->map(fn($a) => [
                    'id' => 'dc_'.$a->id, 'name' => $a->name, 'bot_name' => $a->bot_name,
                ])->values(),
                'tiktok' => $tiktokAccounts->map(fn($a) => [
                    'id' => 'tt_'.$a->id, 'name' => $a->name, 'open_id' => $a->open_id,
                ])->values(),
                'line' => $lineAccounts->map(fn($a) => [
                    'id' => 'line_'.$a->id, 'name' => $a->name, 'channel_id' => $a->channel_id,
                ])->values(),
                'twitter' => $twitterAccounts->map(fn($a) => [
                    'id' => 'x_'.$a->id, 'name' => $a->name, 'username' => $a->username,
                ])->values(),
                'sms' => \App\Models\WaTwilioAccount::where('user_id', Auth::id())
                    ->where('is_active', true)->get()->map(fn($a) => [
                        'id' => 'sms_'.$a->id, 'name' => $a->name, 'phone_number' => $a->phone_number,
                    ])->values(),
                'email' => \App\Models\WaSendGridAccount::where('user_id', Auth::id())
                    ->where('is_active', true)->get()->map(fn($a) => [
                        'id' => 'email_'.$a->id, 'name' => $a->name, 'from_email' => $a->from_email,
                    ])->values(),
            ],
        ]);
    }

    public function send(Request $request, WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $channel = $this->detectChannel($contact);
        $userId = Auth::id();

        $isWhatsApp = ChannelRegistry::isWhatsAppVariant($channel);
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

            case 'gbm':
                $gbmAccount = WaGbmAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$gbmAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun GBM tidak tersedia'], 422);
                }
                $convoId = str_replace('gbm:', '', $contact->phone);
                $result = $this->gbm->sendMessage($gbmAccount, $convoId, $request->message);
                break;

            case 'discord':
                $dcAccount = WaDiscordAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$dcAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun Discord tidak tersedia'], 422);
                }
                $dcId = str_replace('dc:', '', $contact->phone);
                $result = $this->discord->sendMessage($dcAccount, $dcId, $request->message);
                break;

            case 'facebook':
                $fbAccount = WaFacebookAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$fbAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun Facebook tidak tersedia'], 422);
                }
                $fbId = str_replace('fb:', '', $contact->phone);
                $result = $this->facebook->sendMessage($fbAccount, $fbId, $request->message);
                break;

            case 'tiktok':
                $ttAccount = \App\Models\WaTiktokAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$ttAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun TikTok tidak tersedia'], 422);
                }
                $ttOpenId = str_replace('tt:', '', $contact->phone);
                $result = app(\App\Services\TikTokService::class)->sendMessage($ttAccount->access_token, $ttOpenId, $request->message);
                $messageType = 'tiktok';
                break;

            case 'line':
                $lineAccount = \App\Models\WaLineAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$lineAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun LINE tidak tersedia'], 422);
                }
                $lineUserId = str_replace('line:', '', $contact->phone);
                $result = app(\App\Services\LineService::class)->pushMessage($lineAccount, $lineUserId, $request->message);
                $messageType = 'line';
                break;

            case 'twitter':
                $twAccount = \App\Models\WaTwitterAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$twAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun X/Twitter tidak tersedia'], 422);
                }
                $twId = str_replace('x:', '', $contact->phone);
                $result = app(\App\Services\TwitterService::class)->sendDM($twAccount->access_token, $twId, $request->message);
                $messageType = 'twitter';
                break;

            case 'sms':
                $smsAccount = \App\Models\WaTwilioAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$smsAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun SMS/Twilio tidak tersedia'], 422);
                }
                $smsTo = str_replace('sms:', '', $contact->phone);
                $smsResult = app(\App\Services\TwilioService::class)->sendSms($smsAccount, $smsTo, $request->message);
                $result = ['ok' => !($smsResult['error'] ?? false) && empty($smsResult['error_code']), 'error' => $smsResult['error_message'] ?? null];
                $messageType = 'sms';
                break;

            case 'email':
                $sgAccount = \App\Models\WaSendGridAccount::where('user_id', $userId)->where('is_active', true)->first();
                if (!$sgAccount) {
                    return response()->json(['ok' => false, 'error' => 'Akun Email/SendGrid tidak tersedia'], 422);
                }
                $emailTo = str_replace('email:', '', $contact->phone);
                $emailResult = app(\App\Services\SendGridService::class)->sendEmail($sgAccount, $emailTo, 'Message from WABot', $request->message);
                $result = ['ok' => $emailResult['ok'] ?? false, 'error' => $emailResult['error'] ?? null];
                $messageType = 'email';
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
            'telegram', 'gbm', 'discord', 'tiktok', 'line', 'twitter' => $result['ok'] ?? false,
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
                    foreach (ChannelRegistry::channelPhonePatterns() as $pattern) {
                        $q->orWhere('phone', 'like', $pattern);
                    }
                });
            })
            ->get()
            ->map(function ($contact) {
                $lastMsg = WaMessage::where('contact_id', $contact->id)
                    ->latest()
                    ->first();
                $displayPhone = $contact->display_phone;
        if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:') && !str_starts_with($contact->phone, 'sms:') && !str_starts_with($contact->phone, 'email:') && !str_starts_with($contact->phone, 'gbm:') && !str_starts_with($contact->phone, 'dc:')) {
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
                    'channel' => ChannelRegistry::getByPhone($contact->phone),
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
        if (!$displayPhone && !str_contains($contact->phone, '@lid') && !str_starts_with($contact->phone, 'ig:') && !str_starts_with($contact->phone, 'tg:') && !str_starts_with($contact->phone, 'fb:') && !str_starts_with($contact->phone, 'gbm:') && !str_starts_with($contact->phone, 'dc:') && !str_starts_with($contact->phone, 'tt:') && !str_starts_with($contact->phone, 'line:') && !str_starts_with($contact->phone, 'x:') && !str_starts_with($contact->phone, 'sms:') && !str_starts_with($contact->phone, 'email:')) {
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
