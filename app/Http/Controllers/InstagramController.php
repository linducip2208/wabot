<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaInstagramAccount;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Services\AiService;
use App\Services\InstagramService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InstagramController extends Controller
{
    public function __construct(
        protected InstagramService $instagram,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaInstagramAccount::where('user_id', Auth::id())->latest()->get();
        return view('instagram.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'app_id' => 'required|string|max:100',
            'app_secret' => 'required|string|max:200',
            'webhook_verify_token' => 'nullable|string|max:100',
        ]);

        WaInstagramAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'app_id' => $validated['app_id'],
            'app_secret' => $validated['app_secret'],
            'webhook_verify_token' => $validated['webhook_verify_token'] ?? null,
            'status' => 'disconnected',
        ]);

        return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_added'));
    }

    public function connect(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $redirectUri = route('instagram.callback');
        $url = $this->instagram->getAuthUrl($account, $redirectUri);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('instagram.index')->with('error', __('messages.error.instagram_authorization_cancelled'));
        }

        $accounts = WaInstagramAccount::where('user_id', Auth::id())->get();

        foreach ($accounts as $account) {
            $tokenData = $this->instagram->exchangeToken($account, $code, route('instagram.callback'));

            if ($tokenData && isset($tokenData['access_token'])) {
                $account->update([
                    'access_token' => $tokenData['access_token'],
                    'instagram_id' => $tokenData['user_id'] ?? null,
                    'status' => 'connected',
                    'last_active_at' => now(),
                ]);

                $longToken = $this->instagram->getLongLivedToken($account);
                if ($longToken && isset($longToken['access_token'])) {
                    $account->update(['access_token' => $longToken['access_token']]);
                }

                return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_connected'));
            }
        }

        return redirect()->route('instagram.index')->with('error', __('messages.error.instagram_connection_failed'));
    }

    public function disconnect(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['status' => 'disconnected', 'is_active' => false]);
        return back()->with('success', __('messages.success.instagram_disconnected'));
    }

    public function destroy(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_deleted'));
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
                $message = $messaging['message']['text'] ?? null;

                if ($sender && $recipient && $message) {
                    $account = WaInstagramAccount::where('instagram_id', $recipient)->first();
                    if ($account) {
                        $this->handleDM($account, $sender, $message);
                    }
                }
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') === 'comments') {
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

        $accounts = WaInstagramAccount::whereNotNull('webhook_verify_token')->get();
        foreach ($accounts as $account) {
            if ($mode === 'subscribe' && $token === $account->webhook_verify_token) {
                return response($challenge, 200);
            }
        }

        return response('Verification failed', 403);
    }

    protected function handleDM(WaInstagramAccount $account, string $senderId, string $text): void
    {
        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => 'ig:' . $senderId],
            ['name' => 'IG: ' . $senderId, 'display_phone' => 'IG DM']
        );

        $incoming = WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'instagram',
            'channel' => 'instagram',
            'message' => $text,
            'phone' => 'ig:' . $senderId,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        // ── Sentiment analysis ───────────────────────────────────
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId);
            } catch (\Throwable) {}
        }

        // ── Intent detection ─────────────────────────────────────
        try {
            $detectedIntent = $this->intent->detect($userId, $text, 'instagram');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        // ── SLA tracking ─────────────────────────────────────────
        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        // ── Team inbox assignment ────────────────────────────────
        try {
            $this->teamInbox->autoAssign($contact->id, 0);
        } catch (\Throwable) {}

        // ── Welcome message ──────────────────────────────────────
        $this->checkWelcome($account, $contact, $senderId);

        // ── AI agent trigger (intent detection) ──────────────────
        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $senderId, $text, $defaultAiKey);
            return;
        }

        // ── Keyword auto-reply ───────────────────────────────────
        if ($this->handleKeywordReply($account, $contact, $senderId, $text)) {
            return;
        }

        // ── Fallback ─────────────────────────────────────────────
        $this->handleFallback($account, $contact, $senderId, $text);
    }

    protected function checkWelcome(WaInstagramAccount $account, WaContact $contact, string $senderId): void
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

        $result = $this->instagram->sendDM($senderId, $account->access_token, $welcomeText);

        if (!isset($result['error'])) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'instagram',
                'channel' => 'instagram',
                'message' => $welcomeRule->reply_message,
                'phone' => 'ig:' . $senderId,
                'status' => 'sent',
            ]);

            Log::info("Instagram welcome sent", [
                'sender_id' => $senderId,
                'name' => $contact->name,
            ]);
        }
    }

    protected function handleAiAgent(WaInstagramAccount $account, WaContact $contact, string $senderId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->instagram->sendDM($senderId, $account->access_token, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'instagram',
                    'channel' => 'instagram',
                    'message' => $reply,
                    'phone' => 'ig:' . $senderId,
                    'status' => isset($result['error']) ? 'failed' : 'sent',
                ]);

                Log::info("Instagram AI agent reply sent", ['sender_id' => $senderId]);
            }
        } catch (\Exception $e) {
            Log::error('Instagram AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaInstagramAccount $account, WaContact $contact, string $senderId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $senderId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Instagram AI auto-reply failed: ' . $e->getMessage());
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

        $result = $this->instagram->sendDM($senderId, $account->access_token, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'instagram',
            'channel' => 'instagram',
            'message' => $replyText,
            'phone' => 'ig:' . $senderId,
            'status' => isset($result['error']) ? 'failed' : 'sent',
        ]);

        Log::info("Instagram keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'sender_id' => $senderId,
            'ai' => $rule->use_ai,
        ]);

        // ── SLA: record agent response ───────────────────────────
        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleFallback(WaInstagramAccount $account, WaContact $contact, string $senderId, string $text): void
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
            Log::info("Instagram fallback cooldown active", ['sender_id' => $senderId]);
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

        $result = $this->instagram->sendDM($senderId, $account->access_token, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'instagram',
            'message' => $replyText,
            'phone' => 'ig:' . $senderId,
            'status' => isset($result['error']) ? 'failed' : 'sent',
        ]);

        Log::info("Instagram fallback reply sent", [
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
        $commentText = $value['text'] ?? '';
        $mediaId = $value['media']['id'] ?? null;
        $fromId = $value['from']['id'] ?? null;
        $fromName = $value['from']['username'] ?? 'Instagram User';

        if (!$commentId || !$fromId) return;

        $account = WaInstagramAccount::where('status', 'connected')
            ->where('is_active', true)
            ->first();

        if (!$account) return;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => 'ig:' . $fromId],
            ['name' => $fromName, 'display_phone' => 'IG Comment']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'instagram',
            'channel' => 'instagram',
            'message' => '[Comment] ' . $commentText,
            'phone' => 'ig:' . $fromId,
            'status' => 'delivered',
        ]);

        $rule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->whereIn('match_type', ['contains', 'exact', 'starts_with'])
            ->whereNull('session_id')
            ->get()
            ->first(fn($r) => $r->matches($commentText));

        if ($rule) {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $fromName,
                'phone' => $fromId,
            ]);

            $this->instagram->sendDM(
                $account->instagram_id,
                $account->access_token,
                $replyText,
                $fromId
            );

            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'instagram',
                'channel' => 'instagram',
                'message' => '[Comment Reply] ' . $replyText,
                'phone' => 'ig:' . $fromId,
                'status' => 'sent',
            ]);

            Log::info("Instagram comment auto-reply sent", [
                'comment_id' => $commentId,
                'from' => $fromName,
            ]);
        }
    }
}
