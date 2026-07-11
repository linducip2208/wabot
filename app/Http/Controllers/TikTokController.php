<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaTiktokAccount;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use App\Services\TikTokService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TikTokController extends Controller
{
    public function __construct(
        protected TikTokService $tiktok,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaTiktokAccount::where('user_id', Auth::id())->latest()->get();
        return view('tiktok.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_key' => 'required|string|max:255',
            'client_secret' => 'required|string|max:500',
        ]);

        WaTiktokAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'client_key' => $validated['client_key'],
            'client_secret' => $validated['client_secret'],
        ]);

        return redirect()->route('tiktok.index')->with('success', __('messages.success.tiktok_added'));
    }

    public function connect(WaTiktokAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $state = Str::random(40);
        session(['tiktok_state' => $state, 'tiktok_account_id' => $account->id]);

        $redirectUri = route('tiktok.callback');
        $url = $this->tiktok->getAuthUrl($account->client_key, $redirectUri, $state);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$code || $state !== session('tiktok_state')) {
            return redirect()->route('tiktok.index')->with('error', 'TikTok authorization failed.');
        }

        $accountId = session('tiktok_account_id');
        $account = WaTiktokAccount::findOrFail($accountId);
        abort_if($account->user_id !== Auth::id(), 403);

        $redirectUri = route('tiktok.callback');
        $tokenData = $this->tiktok->exchangeToken(
            $account->client_key,
            $account->client_secret,
            $code,
            $redirectUri
        );

        if (!$tokenData || empty($tokenData['access_token'])) {
            return redirect()->route('tiktok.index')->with('error', __('messages.error.tiktok_connection_failed'));
        }

        $expiresIn = $tokenData['expires_in'] ?? 86400;
        $openId = $tokenData['open_id'] ?? null;

        $account->update([
            'open_id' => $openId,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return redirect()->route('tiktok.index')->with('success', __('messages.success.tiktok_connected'));
    }

    public function disconnect(WaTiktokAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['is_active' => false]);
        return back()->with('success', __('messages.success.tiktok_disconnected'));
    }

    public function destroy(WaTiktokAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('tiktok.index')->with('success', __('messages.success.tiktok_deleted'));
    }

    public function testSend(Request $request, WaTiktokAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'open_id' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        $result = $this->tiktok->sendMessage($account->access_token, $validated['open_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }
        return back()->with('error', __('messages.error.tiktok_failed', ['error' => $result['error'] ?? 'Unknown']));
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        if (isset($payload['type']) && $payload['type'] === 'challenge') {
            return response($payload['challenge'] ?? '', 200);
        }

        $openId = $payload['open_id'] ?? $payload['sender_id'] ?? null;
        $text = $payload['text'] ?? $payload['content']['text'] ?? $payload['message']['text'] ?? '';
        $senderName = $payload['sender_name'] ?? $payload['user']['display_name'] ?? 'TikTok User';
        $senderId = 'tt:' . $openId;

        if (empty($text)) return response('ok');

        $account = WaTiktokAccount::where('is_active', true)->first();
        if (!$account) return response('ok');

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => 'TikTok DM']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'tiktok',
            'channel' => 'tiktok',
            'message' => $text,
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        $this->processIncomingMessage($account, $contact, $openId, $text, $userId);

        return response('ok');
    }

    protected function processIncomingMessage(WaTiktokAccount $account, WaContact $contact, string $openId, string $text, int $userId): void
    {
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)->first();
        if ($defaultAiKey) {
            try { $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId); } catch (\Throwable) {}
        }

        try { $detectedIntent = $this->intent->detect($userId, $text, 'tiktok'); } catch (\Throwable) { $detectedIntent = null; }
        try { $this->sla->start($userId, $contact->id); } catch (\Throwable) {}
        try { $this->teamInbox->autoAssign($contact->id, 0); } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $openId);

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $openId, $text, $defaultAiKey);
            return;
        }

        if ($this->handleKeywordReply($account, $contact, $openId, $text)) return;
        $this->handleFallback($account, $contact, $openId, $text);
    }

    protected function checkWelcome(WaTiktokAccount $account, WaContact $contact, string $openId): void
    {
        $lastOutgoing = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->max('created_at');
        $lastOutgoing = $lastOutgoing ? \Illuminate\Support\Carbon::parse($lastOutgoing) : null;
        if ($lastOutgoing && $lastOutgoing->diffInHours(now()) < 24) return;

        $welcomeRule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'welcome')
            ->whereNull('session_id')
            ->first();
        if (!$welcomeRule) return;

        $welcomeText = $this->spintax->process($welcomeRule->reply_message, [
            'name' => $contact->name, 'phone' => $openId,
        ]);

        $result = $this->tiktok->sendMessage($account->access_token, $openId, $welcomeText);
        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'tiktok',
                'channel' => 'tiktok',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);
        }
    }

    protected function handleAiAgent(WaTiktokAccount $account, WaContact $contact, string $openId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);
            if ($reply) {
                $result = $this->tiktok->sendMessage($account->access_token, $openId, $reply);
                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'tiktok',
                    'channel' => 'tiktok',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('TikTok AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaTiktokAccount $account, WaContact $contact, string $openId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);
        if (!$rule) return false;

        $replyText = $this->buildReplyText($rule, $text, $contact, $openId);
        $result = $this->tiktok->sendMessage($account->access_token, $openId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'tiktok',
            'channel' => 'tiktok',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        try { $this->sla->recordResponse($account->user_id, $contact->id); } catch (\Throwable) {}
        return true;
    }

    protected function handleFallback(WaTiktokAccount $account, WaContact $contact, string $openId, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->whereNull('session_id')
            ->first();
        if (!$fallback) return;

        $replyText = $this->buildReplyText($fallback, $text, $contact, $openId);
        if (!$replyText) return;

        $result = $this->tiktok->sendMessage($account->access_token, $openId, $replyText);
        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'tiktok',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);
    }

    protected function buildReplyText(WaAutoreply $rule, string $text, WaContact $contact, string $openId): string
    {
        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($contact->user_id ?? \App\Models\WaAiKey::find($rule->aiKey)?->user_id ?? 0);
                $reply = $this->ai->send($rule->aiKey, $text, $kb);
                if ($reply) return $reply;
            } catch (\Exception) {}
        }
        return $this->spintax->process($rule->reply_message, [
            'name' => $contact->name,
            'phone' => $openId,
        ]);
    }

    protected function findAutoReply(int $userId, string $incomingMessage): ?WaAutoreply
    {
        $rules = WaAutoreply::where('user_id', $userId)
            ->where('is_active', true)
            ->whereNotIn('match_type', ['welcome', 'fallback'])
            ->whereNull('session_id')
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($incomingMessage)) return $rule;
        }
        return null;
    }
}
