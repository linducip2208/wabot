<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaGbmAccount;
use App\Models\WaMessage;
use App\Services\AiService;
use App\Services\GbmService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GbmController extends Controller
{
    public function __construct(
        protected GbmService $gbm,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaGbmAccount::where('user_id', Auth::id())->latest()->get();
        return view('gbm.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'nullable|string|max:100',
            'agent_id' => 'nullable|string|max:100',
            'service_account_json' => 'required|string',
        ]);

        json_decode($validated['service_account_json'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->with('error', 'Invalid JSON for service account key.');
        }

        WaGbmAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'brand_id' => $validated['brand_id'] ?? null,
            'agent_id' => $validated['agent_id'] ?? null,
            'service_account_json' => $validated['service_account_json'],
        ]);

        return redirect()->route('gbm.index')->with('success', __('messages.success.gbm_added'));
    }

    public function connect(WaGbmAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $testResult = $this->gbm->call($account, 'conversations/test-convo-/messages', [
            'messageId' => uniqid('gbm-test-', true),
            'text' => 'Connection test',
            'representative' => [
                'representativeType' => 'BOT',
                'displayName' => $account->name,
            ],
            'fallback' => 'LIVE',
        ]);

        if (!($testResult['ok'] ?? false)) {
            return back()->with('error', __('messages.error.gbm_connection_failed', ['error' => $testResult['error'] ?? 'Unknown']));
        }

        $account->update([
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return back()->with('success', __('messages.success.gbm_connected'));
    }

    public function disconnect(WaGbmAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $account->update(['is_active' => false]);

        return back()->with('success', __('messages.success.gbm_disconnected'));
    }

    public function destroy(WaGbmAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('gbm.index')->with('success', __('messages.success.gbm_deleted'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        $message = $payload['message'] ?? null;
        if (!$message) return response('ok');

        $conversationId = $payload['conversationId'] ?? null;
        $text = $message['text'] ?? '';
        $senderName = $message['representative']['displayName'] ?? 'GBM User';
        $senderId = 'gbm:' . ($conversationId ?? 'unknown');

        if (!$conversationId) return response('ok');

        $account = WaGbmAccount::where('is_active', true)->first();
        if (!$account) return response('ok');

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => 'GBM: ' . $conversationId]
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'gbm',
            'channel' => 'gbm',
            'message' => $text,
            'phone' => $senderId,
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
            $detectedIntent = $this->intent->detect($userId, $text, 'gbm');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        try {
            $this->teamInbox->autoAssign($contact->id, 0);
        } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $conversationId);

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $conversationId, $text, $defaultAiKey);
            return response('ok');
        }

        if ($this->handleKeywordReply($account, $contact, $conversationId, $text)) {
            return response('ok');
        }

        $this->handleFallback($account, $contact, $conversationId, $text);

        return response('ok');
    }

    protected function checkWelcome(WaGbmAccount $account, WaContact $contact, string $conversationId): void
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
            'phone' => $conversationId,
        ]);

        $result = $this->gbm->sendMessage($account, $conversationId, $welcomeText);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'gbm',
                'channel' => 'gbm',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);

            Log::info("GBM welcome sent", [
                'conversation_id' => $conversationId,
                'name' => $contact->name,
            ]);
        }
    }

    protected function handleAiAgent(WaGbmAccount $account, WaContact $contact, string $conversationId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->gbm->sendMessage($account, $conversationId, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'gbm',
                    'channel' => 'gbm',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);

                Log::info("GBM AI agent reply sent", ['conversation_id' => $conversationId]);
            }
        } catch (\Exception $e) {
            Log::error('GBM AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaGbmAccount $account, WaContact $contact, string $conversationId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $conversationId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('GBM AI auto-reply failed: ' . $e->getMessage());
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $conversationId,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $conversationId,
            ]);
        }

        $result = $this->gbm->sendMessage($account, $conversationId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'gbm',
            'channel' => 'gbm',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("GBM keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'conversation_id' => $conversationId,
            'ai' => $rule->use_ai,
        ]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleFallback(WaGbmAccount $account, WaContact $contact, string $conversationId, string $text): void
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
            Log::info("GBM fallback cooldown active", ['conversation_id' => $conversationId]);
            return;
        }

        if ($fallback->use_ai && $fallback->aiKey) {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $replyText = $this->ai->send($fallback->aiKey, $text, $kb ?: null);
        } else {
            $replyText = $this->spintax->process($fallback->reply_message, [
                'name' => $contact->name,
                'phone' => $conversationId,
            ]);
        }

        if (!$replyText) return;

        $result = $this->gbm->sendMessage($account, $conversationId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'gbm',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("GBM fallback reply sent", [
            'conversation_id' => $conversationId,
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
}
