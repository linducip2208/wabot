<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaFacebookAccount;
use App\Models\WaMessage;
use App\Services\AiService;
use App\Services\FacebookService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    public function __construct(
        protected FacebookService $facebook,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaFacebookAccount::where('user_id', Auth::id())->latest()->get();
        return view('facebook.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'page_id' => 'required|string|max:100',
            'page_token' => 'required|string|max:500',
            'app_secret' => 'nullable|string|max:200',
        ]);

        WaFacebookAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'page_id' => $validated['page_id'],
            'page_token' => $validated['page_token'],
            'app_secret' => $validated['app_secret'] ?? null,
            'status' => 'disconnected',
        ]);

        return redirect()->route('facebook.index')->with('success', __('messages.success.facebook_added'));
    }

    public function connect(WaFacebookAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $pageInfo = $this->facebook->getPageInfo($account->page_id, $account->page_token);

        if (!$pageInfo || empty($pageInfo['id'])) {
            return back()->with('error', __('messages.error.facebook_connection_failed'));
        }

        $account->update([
            'page_name' => $pageInfo['name'] ?? null,
            'status' => 'connected',
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return back()->with('success', __('messages.success.facebook_connected', ['name' => $pageInfo['name'] ?? $account->page_id]));
    }

    public function disconnect(WaFacebookAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['status' => 'disconnected', 'is_active' => false]);
        return back()->with('success', __('messages.success.facebook_disconnected'));
    }

    public function destroy(WaFacebookAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('facebook.index')->with('success', __('messages.success.facebook_deleted'));
    }

    public function webhook(Request $request)
    {
        if ($request->method() === 'GET') {
            return $this->verifyWebhook($request);
        }

        $body = $request->all();

        foreach ($body['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $messaging) {
                $sender = $messaging['sender']['id'] ?? null;
                $recipient = $messaging['recipient']['id'] ?? null;
                $messageText = $messaging['message']['text'] ?? null;

                if ($sender && $recipient && $messageText) {
                    $account = WaFacebookAccount::where('page_id', $recipient)
                        ->where('status', 'connected')
                        ->where('is_active', true)
                        ->first();

                    if ($account) {
                        $this->handleMessage($account, $sender, $messageText);
                    }
                }
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') === 'feed') {
                    $this->handleComment($change['value'] ?? []);
                }
            }
        }

        return response('ok', 200);
    }

    protected function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $accounts = WaFacebookAccount::whereNotNull('app_secret_encrypted')
            ->where('is_active', true)
            ->get();

        foreach ($accounts as $account) {
            if ($mode === 'subscribe' && $token === $account->app_secret) {
                return response($challenge, 200);
            }
        }

        return response('Verification failed', 403);
    }

    protected function handleMessage(WaFacebookAccount $account, string $senderId, string $text): void
    {
        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => 'fb:' . $senderId],
            ['name' => 'FB: ' . $senderId, 'display_phone' => 'FB Messenger']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'facebook',
            'channel' => 'facebook',
            'message' => $text,
            'phone' => 'fb:' . $senderId,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId);
            } catch (\Throwable) {}
        }

        try {
            $detectedIntent = $this->intent->detect($userId, $text, 'facebook');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        try {
            $this->teamInbox->autoAssign($contact->id, 0);
        } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $senderId);

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $senderId, $text, $defaultAiKey);
            return;
        }

        if ($this->handleKeywordReply($account, $contact, $senderId, $text)) {
            return;
        }

        $this->handleFallback($account, $contact, $senderId, $text);
    }

    protected function checkWelcome(WaFacebookAccount $account, WaContact $contact, string $senderId): void
    {
        $lastOutgoing = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->max('created_at');

        $lastOutgoing = $lastOutgoing ? \Illuminate\Support\Carbon::parse($lastOutgoing) : null;
        $canSendWelcome = !$lastOutgoing || $lastOutgoing->diffInHours(now()) >= 24;

        if (!$canSendWelcome) return;

        $welcomeRule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'welcome')
            ->whereNull('session_id')
            ->first();

        if (!$welcomeRule) return;

        $welcomeText = $this->spintax->process($welcomeRule->reply_message, [
            'name' => $contact->name,
            'phone' => $senderId,
        ]);

        $result = $this->facebook->sendMessage($account, $senderId, $welcomeText);

        if (!isset($result['error'])) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'facebook',
                'channel' => 'facebook',
                'message' => $welcomeRule->reply_message,
                'phone' => 'fb:' . $senderId,
                'status' => 'sent',
            ]);

            Log::info("Facebook welcome sent", [
                'sender_id' => $senderId,
                'name' => $contact->name,
            ]);
        }
    }

    protected function handleAiAgent(WaFacebookAccount $account, WaContact $contact, string $senderId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->facebook->sendMessage($account, $senderId, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'facebook',
                    'channel' => 'facebook',
                    'message' => $reply,
                    'phone' => 'fb:' . $senderId,
                    'status' => isset($result['error']) ? 'failed' : 'sent',
                ]);

                Log::info("Facebook AI agent reply sent", ['sender_id' => $senderId]);
            }
        } catch (\Exception $e) {
            Log::error('Facebook AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaFacebookAccount $account, WaContact $contact, string $senderId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Sorry, I cannot answer right now.', [
                        'name' => $contact->name, 'phone' => $senderId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Facebook AI auto-reply failed: ' . $e->getMessage());
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $senderId,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $senderId,
            ]);
        }

        $result = $this->facebook->sendMessage($account, $senderId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'facebook',
            'channel' => 'facebook',
            'message' => $replyText,
            'phone' => 'fb:' . $senderId,
            'status' => isset($result['error']) ? 'failed' : 'sent',
        ]);

        Log::info("Facebook keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'sender_id' => $senderId,
            'ai' => $rule->use_ai,
        ]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleFallback(WaFacebookAccount $account, WaContact $contact, string $senderId, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->whereNull('session_id')
            ->first();

        if (!$fallback) return;

        $recentFallbacks = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->where('type', 'fallback')
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentFallbacks >= 3) {
            Log::info("Facebook fallback cooldown active", ['sender_id' => $senderId]);
            return;
        }

        if ($fallback->use_ai && $fallback->aiKey) {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $replyText = $this->ai->send($fallback->aiKey, $text, $kb ?: null);
        } else {
            $replyText = $this->spintax->process($fallback->reply_message, [
                'name' => $contact->name,
                'phone' => $senderId,
            ]);
        }

        if (!$replyText) return;

        $result = $this->facebook->sendMessage($account, $senderId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'facebook',
            'message' => $replyText,
            'phone' => 'fb:' . $senderId,
            'status' => isset($result['error']) ? 'failed' : 'sent',
        ]);

        Log::info("Facebook fallback reply sent", [
            'sender_id' => $senderId,
            'ai' => (bool) $fallback->use_ai,
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
            if ($rule->matches($incomingMessage)) {
                return $rule;
            }
        }

        return null;
    }

    protected function handleComment(array $value): void
    {
        $commentId = $value['id'] ?? null;
        $commentText = $value['message'] ?? '';
        $postId = $value['post_id'] ?? null;
        $fromId = $value['from']['id'] ?? null;
        $fromName = $value['from']['name'] ?? 'Facebook User';

        if (!$commentId || !$fromId) return;

        $account = WaFacebookAccount::where('status', 'connected')
            ->where('is_active', true)
            ->first();

        if (!$account) return;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => 'fb:' . $fromId],
            ['name' => $fromName, 'display_phone' => 'FB Comment']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'facebook',
            'channel' => 'facebook',
            'message' => '[Comment] ' . $commentText,
            'phone' => 'fb:' . $fromId,
            'status' => 'delivered',
        ]);

        $rule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->whereIn('match_type', ['comment', 'contains', 'exact', 'starts_with'])
            ->whereNull('session_id')
            ->get()
            ->first(fn($r) => $r->matches($commentText));

        if ($rule) {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $fromName,
                'phone' => $fromId,
            ]);

            $this->facebook->replyToComment($commentId, $account->page_token, $replyText);

            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'facebook',
                'channel' => 'facebook',
                'message' => '[Comment Reply] ' . $replyText,
                'phone' => 'fb:' . $fromId,
                'status' => 'sent',
            ]);

            Log::info("Facebook comment auto-reply sent", [
                'comment_id' => $commentId,
                'from' => $fromName,
            ]);
        }
    }
}
