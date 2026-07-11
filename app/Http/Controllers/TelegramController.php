<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaTelegramAccount::where('user_id', Auth::id())->latest()->get();
        return view('telegram.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bot_token' => 'required|string|max:200',
        ]);

        $account = WaTelegramAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'bot_token' => $validated['bot_token'],
            'status' => 'disconnected',
        ]);

        return redirect()->route('telegram.index')->with('success', __('messages.success.telegram_added'));
    }

    public function connect(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $me = $this->telegram->getMe($account);

        if (!$me) {
            return back()->with('error', __('messages.error.telegram_connection_failed'));
        }

        $webhookUrl = route('webhook.telegram', ['account' => $account->id]);

        $this->telegram->setWebhook($account, $webhookUrl);

        $account->update([
            'bot_username' => $me['username'] ?? null,
            'bot_id' => (string) ($me['id'] ?? ''),
            'status' => 'connected',
            'is_active' => true,
            'last_active_at' => now(),
        ]);

        return back()->with('success', __('messages.success.telegram_connected', ['username' => ($me['username'] ?? 'unknown')]));
    }

    public function disconnect(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $this->telegram->deleteWebhook($account);

        $account->update(['status' => 'disconnected', 'is_active' => false]);

        return back()->with('success', __('messages.success.telegram_disconnected'));
    }

    public function testSend(Request $request, WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'chat_id' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        $result = $this->telegram->sendMessage($account, $validated['chat_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        return back()->with('error', __('messages.error.telegram_failed', ['error' => ($result['description'] ?? 'Unknown')]));
    }

    public function destroy(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('telegram.index')->with('success', __('messages.success.telegram_deleted'));
    }

    public function webhook(Request $request, WaTelegramAccount $account)
    {
        $update = $request->all();

        if (!empty($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query'], $account);
        }

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) return response('ok');

        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];
        $senderName = $from['first_name'] ?? 'Telegram User';
        $senderId = 'tg:' . ($from['id'] ?? $chatId);

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => '@' . ($from['username'] ?? $chatId)]
        );

        $mediaUrl = null;
        $messageText = $text;

        if (!empty($message['photo'])) {
            $lastPhoto = end($message['photo']);
            $fileId = $lastPhoto['file_id'] ?? null;
            $caption = $message['caption'] ?? '';
            $mediaUrl = $fileId ? $this->telegram->getFileUrl($account, $fileId) : null;
            $messageText = $caption ?: '[Photo]';
        } elseif (!empty($message['video'])) {
            $fileId = $message['video']['file_id'] ?? null;
            $caption = $message['caption'] ?? '';
            $mediaUrl = $fileId ? $this->telegram->getFileUrl($account, $fileId) : null;
            $messageText = $caption ?: '[Video]';
        } elseif (!empty($message['document'])) {
            $fileId = $message['document']['file_id'] ?? null;
            $docName = $message['document']['file_name'] ?? 'Document';
            $caption = $message['caption'] ?? '';
            $mediaUrl = $fileId ? $this->telegram->getFileUrl($account, $fileId) : null;
            $messageText = $caption ?: "[Document: {$docName}]";
        }

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'telegram',
            'channel' => 'telegram',
            'message' => $messageText,
            'phone' => $senderId,
            'media_url' => $mediaUrl,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        // ── Sentiment analysis ───────────────────────────────────
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $messageText, $contact->id, $userId);
            } catch (\Throwable) {}
        }

        // ── Intent detection ─────────────────────────────────────
        try {
            $detectedIntent = $this->intent->detect($userId, $messageText, 'telegram');
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
        $this->checkWelcome($account, $contact, $chatId);

        // ── AI agent trigger (intent detection) ──────────────────
        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $chatId, $messageText, $defaultAiKey);
            return response('ok');
        }

        // ── Keyword auto-reply ───────────────────────────────────
        if ($this->handleKeywordReply($account, $contact, $chatId, $messageText)) {
            return response('ok');
        }

        // ── Fallback ─────────────────────────────────────────────
        $this->handleFallback($account, $contact, $chatId, $messageText);

        return response('ok');
    }

    protected function handleCallbackQuery(array $callbackQuery, WaTelegramAccount $account): \Illuminate\Http\Response
    {
        $data = $callbackQuery['data'] ?? '';
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $from = $callbackQuery['from'] ?? [];
        $senderName = $from['first_name'] ?? 'Telegram User';
        $senderId = 'tg:' . ($from['id'] ?? $chatId);

        $callbackId = $callbackQuery['id'] ?? null;
        if ($callbackId) {
            $this->telegram->call($account, 'answerCallbackQuery', ['callback_query_id' => $callbackId]);
        }

        Log::info('Telegram callback_query received', compact('data', 'chatId', 'senderId'));

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => '@' . ($from['username'] ?? $chatId)]
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'telegram',
            'channel' => 'telegram',
            'message' => "[Callback: {$data}]",
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        if ($this->handleKeywordReply($account, $contact, $chatId, $data)) {
            return response('ok');
        }

        $this->handleFallback($account, $contact, $chatId, $data);

        return response('ok');
    }

    protected function checkWelcome(WaTelegramAccount $account, WaContact $contact, string $chatId): void
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
            'phone' => $chatId,
        ]);

        $result = $this->telegram->sendMessage($account, $chatId, $welcomeText);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'telegram',
                'channel' => 'telegram',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);

            Log::info("Telegram welcome sent", [
                'chat_id' => $chatId,
                'name' => $contact->name,
            ]);
        }
    }

    protected function handleAiAgent(WaTelegramAccount $account, WaContact $contact, string $chatId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->telegram->sendMessage($account, $chatId, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'telegram',
                    'channel' => 'telegram',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);

                Log::info("Telegram AI agent reply sent", ['chat_id' => $chatId]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaTelegramAccount $account, WaContact $contact, string $chatId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $chatId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Telegram AI auto-reply failed: ' . $e->getMessage());
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $chatId,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $chatId,
            ]);
        }

        $result = $this->telegram->sendMessage($account, $chatId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'telegram',
            'channel' => 'telegram',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("Telegram keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'chat_id' => $chatId,
            'ai' => $rule->use_ai,
        ]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleFallback(WaTelegramAccount $account, WaContact $contact, string $chatId, string $text): void
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
            Log::info("Telegram fallback cooldown active", ['chat_id' => $chatId]);
            return;
        }

        if ($fallback->use_ai && $fallback->aiKey) {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $replyText = $this->ai->send($fallback->aiKey, $text, $kb ?: null);
        } else {
            $replyText = $this->spintax->process($fallback->reply_message, [
                'name' => $contact->name,
                'phone' => $chatId,
            ]);
        }

        if (!$replyText) return;

        $result = $this->telegram->sendMessage($account, $chatId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'telegram',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("Telegram fallback reply sent", [
            'chat_id' => $chatId,
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
