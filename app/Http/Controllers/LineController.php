<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaLineAccount;
use App\Models\WaMessage;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\LineService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LineController extends Controller
{
    public function __construct(
        protected LineService $line,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaLineAccount::where('user_id', Auth::id())->latest()->get();
        return view('line.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'channel_id' => 'required|string|max:50',
            'channel_secret' => 'required|string|max:100',
            'channel_access_token' => 'required|string|max:500',
        ]);

        WaLineAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'channel_id' => $validated['channel_id'],
            'channel_secret' => $validated['channel_secret'],
            'channel_access_token' => $validated['channel_access_token'],
        ]);

        return redirect()->route('line.index')->with('success', __('messages.success.line_added'));
    }

    public function connect(WaLineAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $result = $this->line->verifyWebhook($account);

        if ($result['ok'] ?? false) {
            $info = $result['data'] ?? [];
            $account->update([
                'bot_basic_id' => $info['basicId'] ?? $account->bot_basic_id,
                'display_name' => $info['displayName'] ?? $account->display_name,
                'picture_url' => $info['pictureUrl'] ?? $account->picture_url,
                'is_active' => true,
                'connected_at' => now(),
            ]);
            return back()->with('success', __('messages.success.line_connected', ['name' => $account->display_name ?? $account->name]));
        }

        return back()->with('error', __('messages.error.line_connection_failed'));
    }

    public function disconnect(WaLineAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['is_active' => false]);
        return back()->with('success', __('messages.success.line_disconnected'));
    }

    public function destroy(WaLineAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('line.index')->with('success', __('messages.success.line_deleted'));
    }

    public function testSend(Request $request, WaLineAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'user_id' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        $result = $this->line->pushMessage($account, $validated['user_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }
        return back()->with('error', __('messages.error.line_failed', ['error' => $result['error'] ?? 'Unknown']));
    }

    public function richMenuList(WaLineAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $result = $this->line->getRichMenuList($account);
        return response()->json($result);
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-line-signature', '');

        $account = WaLineAccount::where('is_active', true)->first();
        if (!$account) return response('ok');

        $body = $request->getContent();
        if ($account->channel_secret && !$this->line->validateSignature($account, $body, $signature)) {
            Log::warning('LINE webhook signature validation failed');
            return response('NG', 401);
        }

        $events = $request->input('events', []);

        foreach ($events as $event) {
            if (($event['type'] ?? '') !== 'message') continue;

            $replyToken = $event['replyToken'] ?? null;
            $userId = $event['source']['userId'] ?? null;
            $message = $event['message'] ?? [];
            $text = $message['text'] ?? '';
            $messageType = $message['type'] ?? 'text';

            if (empty($text) && $messageType !== 'text') {
                $text = "[{$messageType}]";
            }
            if (empty($text)) continue;

            $senderId = 'line:' . $userId;

            $profile = $this->line->getProfile($account, $userId);
            $senderName = $profile['displayName'] ?? 'LINE User';

            $contact = WaContact::firstOrCreate(
                ['user_id' => $account->user_id, 'phone' => $senderId],
                ['name' => $senderName, 'display_phone' => 'LINE: ' . $senderName]
            );

            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'in',
                'type' => 'line',
                'channel' => 'line',
                'message' => $text,
                'phone' => $senderId,
                'status' => 'delivered',
            ]);

            $userId2 = $account->user_id;

            $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId2)
                ->where('is_active', true)->first();
            if ($defaultAiKey) {
                try { $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId2); } catch (\Throwable) {}
            }

            try { $detectedIntent = $this->intent->detect($userId2, $text, 'line'); } catch (\Throwable) { $detectedIntent = null; }
            try { $this->sla->start($userId2, $contact->id); } catch (\Throwable) {}
            try { $this->teamInbox->autoAssign($contact->id, 0); } catch (\Throwable) {}

            $this->checkWelcome($account, $contact, $userId, $replyToken);

            if ($replyToken) {
                if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
                    $this->handleAiAgentReply($account, $contact, $userId, $replyToken, $text, $defaultAiKey);
                    continue;
                }

                if ($this->handleKeywordReplyReply($account, $contact, $userId, $replyToken, $text)) continue;
                $this->handleFallbackReply($account, $contact, $userId, $replyToken, $text);
            }
        }

        return response('ok');
    }

    protected function checkWelcome(WaLineAccount $account, WaContact $contact, string $userId, ?string $replyToken): void
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
            'name' => $contact->name, 'phone' => $userId,
        ]);

        $result = $replyToken
            ? $this->line->replyMessage($account, $replyToken, $welcomeText)
            : $this->line->pushMessage($account, $userId, $welcomeText);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'line',
                'channel' => 'line',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);
        }
    }

    protected function handleAiAgentReply(WaLineAccount $account, WaContact $contact, string $userId, string $replyToken, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);
            if ($reply) {
                $result = $this->line->replyMessage($account, $replyToken, $reply);
                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'line',
                    'channel' => 'line',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('LINE AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReplyReply(WaLineAccount $account, WaContact $contact, string $userId, string $replyToken, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);
        if (!$rule) return false;

        $replyText = $this->buildReplyText($rule, $text, $contact, $userId);
        $result = $this->line->replyMessage($account, $replyToken, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'line',
            'channel' => 'line',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        try { $this->sla->recordResponse($account->user_id, $contact->id); } catch (\Throwable) {}
        return true;
    }

    protected function handleFallbackReply(WaLineAccount $account, WaContact $contact, string $userId, string $replyToken, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->whereNull('session_id')
            ->first();
        if (!$fallback) return;

        $replyText = $this->buildReplyText($fallback, $text, $contact, $userId);
        if (!$replyText) return;

        $result = $this->line->replyMessage($account, $replyToken, $replyText);
        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'line',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);
    }

    protected function buildReplyText(WaAutoreply $rule, string $text, WaContact $contact, string $userId): string
    {
        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($contact->user_id ?? 0);
                $reply = $this->ai->send($rule->aiKey, $text, $kb);
                if ($reply) return $reply;
            } catch (\Exception) {}
        }
        return $this->spintax->process($rule->reply_message, [
            'name' => $contact->name, 'phone' => $userId,
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
