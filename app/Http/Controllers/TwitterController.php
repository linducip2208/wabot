<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaTwitterAccount;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use App\Services\TwitterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TwitterController extends Controller
{
    public function __construct(
        protected TwitterService $twitter,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaTwitterAccount::where('user_id', Auth::id())->latest()->get();
        return view('twitter.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_id' => 'required|string|max:200',
            'client_secret' => 'required|string|max:500',
        ]);

        WaTwitterAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'client_id' => $validated['client_id'],
            'client_secret' => $validated['client_secret'],
        ]);

        return redirect()->route('twitter.index')->with('success', __('messages.success.twitter_added'));
    }

    public function connect(WaTwitterAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $codeVerifier = TwitterService::generateCodeVerifier();
        $codeChallenge = TwitterService::generateCodeChallenge($codeVerifier);
        $state = Str::random(40);

        session([
            'twitter_code_verifier' => $codeVerifier,
            'twitter_state' => $state,
            'twitter_account_id' => $account->id,
        ]);

        $redirectUri = route('twitter.callback');
        $url = $this->twitter->getAuthUrl($account->client_id, $redirectUri, $codeChallenge, $state);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        if (!$code || $state !== session('twitter_state')) {
            return redirect()->route('twitter.index')->with('error', 'X/Twitter authorization failed.');
        }

        $accountId = session('twitter_account_id');
        $codeVerifier = session('twitter_code_verifier');
        $account = WaTwitterAccount::findOrFail($accountId);
        abort_if($account->user_id !== Auth::id(), 403);

        $redirectUri = route('twitter.callback');
        $tokenData = $this->twitter->exchangeToken(
            $account->client_id,
            $account->client_secret,
            $code,
            $redirectUri,
            $codeVerifier
        );

        if (!$tokenData || empty($tokenData['access_token'])) {
            return redirect()->route('twitter.index')->with('error', __('messages.error.twitter_connection_failed'));
        }

        $me = $this->twitter->getMe($tokenData['access_token']);

        $account->update([
            'twitter_user_id' => $me['data']['id'] ?? $tokenData['user_id'] ?? null,
            'username' => $me['data']['username'] ?? $tokenData['username'] ?? null,
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'is_active' => true,
            'connected_at' => now(),
        ]);

        session()->forget(['twitter_code_verifier', 'twitter_state', 'twitter_account_id']);

        return redirect()->route('twitter.index')->with('success', __('messages.success.twitter_connected', ['name' => $account->username ?? $account->name]));
    }

    public function disconnect(WaTwitterAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['is_active' => false]);
        return back()->with('success', __('messages.success.twitter_disconnected'));
    }

    public function destroy(WaTwitterAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('twitter.index')->with('success', __('messages.success.twitter_deleted'));
    }

    public function testSend(Request $request, WaTwitterAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'recipient_id' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        $result = $this->twitter->sendDM($account->access_token, $validated['recipient_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }
        return back()->with('error', __('messages.error.twitter_failed', ['error' => $result['error'] ?? 'Unknown']));
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        // CRC check for webhook registration
        if (isset($payload['crc_token'])) {
            $account = WaTwitterAccount::where('is_active', true)->first();
            $responseToken = hash_hmac('sha256', $payload['crc_token'], $account->client_secret ?? '');
            return response()->json(['response_token' => 'sha256=' . base64_encode($responseToken)]);
        }

        // Direct message events
        $events = $payload['direct_message_events'] ?? [];

        $account = WaTwitterAccount::where('is_active', true)->first();
        if (!$account) return response('ok');

        foreach ($events as $event) {
            $messageData = $event['message_create'] ?? [];
            $senderId = $event['message_create']['sender_id'] ?? '';
            $text = $messageData['message_data']['text'] ?? '';
            $senderName = 'X User';

            if (empty($text)) continue;
            if ($senderId === ($account->twitter_user_id ?? '')) continue;

            $senderPhone = 'x:' . $senderId;

            $contact = WaContact::firstOrCreate(
                ['user_id' => $account->user_id, 'phone' => $senderPhone],
                ['name' => $senderName, 'display_phone' => 'X DM']
            );

            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'in',
                'type' => 'twitter',
                'channel' => 'twitter',
                'message' => $text,
                'phone' => $senderPhone,
                'status' => 'delivered',
            ]);

            $userId = $account->user_id;
            $this->processIncomingMessage($account, $contact, $senderId, $text, $userId);
        }

        return response('ok');
    }

    protected function processIncomingMessage(WaTwitterAccount $account, WaContact $contact, string $senderId, string $text, int $userId): void
    {
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)->first();
        if ($defaultAiKey) {
            try { $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId); } catch (\Throwable) {}
        }

        try { $detectedIntent = $this->intent->detect($userId, $text, 'twitter'); } catch (\Throwable) { $detectedIntent = null; }
        try { $this->sla->start($userId, $contact->id); } catch (\Throwable) {}
        try { $this->teamInbox->autoAssign($contact->id, 0); } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $senderId);

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $senderId, $text, $defaultAiKey);
            return;
        }

        if ($this->handleKeywordReply($account, $contact, $senderId, $text)) return;
        $this->handleFallback($account, $contact, $senderId, $text);
    }

    protected function checkWelcome(WaTwitterAccount $account, WaContact $contact, string $senderId): void
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
            'name' => $contact->name, 'phone' => $senderId,
        ]);

        $result = $this->twitter->sendDM($account->access_token, $senderId, $welcomeText);
        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'twitter',
                'channel' => 'twitter',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);
        }
    }

    protected function handleAiAgent(WaTwitterAccount $account, WaContact $contact, string $senderId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);
            if ($reply) {
                $result = $this->twitter->sendDM($account->access_token, $senderId, $reply);
                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'twitter',
                    'channel' => 'twitter',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('X/Twitter AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaTwitterAccount $account, WaContact $contact, string $senderId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);
        if (!$rule) return false;

        $replyText = $this->buildReplyText($rule, $text, $contact, $senderId);
        $result = $this->twitter->sendDM($account->access_token, $senderId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'twitter',
            'channel' => 'twitter',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        try { $this->sla->recordResponse($account->user_id, $contact->id); } catch (\Throwable) {}
        return true;
    }

    protected function handleFallback(WaTwitterAccount $account, WaContact $contact, string $senderId, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->whereNull('session_id')
            ->first();
        if (!$fallback) return;

        $replyText = $this->buildReplyText($fallback, $text, $contact, $senderId);
        if (!$replyText) return;

        $result = $this->twitter->sendDM($account->access_token, $senderId, $replyText);
        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'twitter',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);
    }

    protected function buildReplyText(WaAutoreply $rule, string $text, WaContact $contact, string $senderId): string
    {
        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($contact->user_id ?? 0);
                $reply = $this->ai->send($rule->aiKey, $text, $kb);
                if ($reply) return $reply;
            } catch (\Exception) {}
        }
        return $this->spintax->process($rule->reply_message, [
            'name' => $contact->name, 'phone' => $senderId,
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
