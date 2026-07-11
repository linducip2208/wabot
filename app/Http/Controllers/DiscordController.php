<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaDiscordAccount;
use App\Models\WaMessage;
use App\Services\AiService;
use App\Services\DiscordService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiscordController extends Controller
{
    public function __construct(
        protected DiscordService $discord,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaDiscordAccount::where('user_id', Auth::id())->latest()->get();
        return view('discord.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bot_token' => 'required|string|max:200',
            'application_id' => 'nullable|string|max:50',
            'public_key' => 'nullable|string|max:200',
        ]);

        WaDiscordAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'bot_token' => $validated['bot_token'],
            'application_id' => $validated['application_id'] ?? null,
            'public_key' => $validated['public_key'] ?? null,
        ]);

        return redirect()->route('discord.index')->with('success', __('messages.success.discord_added'));
    }

    public function connect(WaDiscordAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $me = $this->discord->getMe($account);

        if (!$me) {
            return back()->with('error', __('messages.error.discord_connection_failed'));
        }

        $account->update([
            'bot_name' => ($me['username'] ?? '#unknown') . '#' . ($me['discriminator'] ?? '0000'),
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return back()->with('success', __('messages.success.discord_connected', ['name' => ($me['username'] ?? 'unknown')]));
    }

    public function disconnect(WaDiscordAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $account->update(['is_active' => false]);

        return back()->with('success', __('messages.success.discord_disconnected'));
    }

    public function destroy(WaDiscordAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('discord.index')->with('success', __('messages.success.discord_deleted'));
    }

    public function testSend(Request $request, WaDiscordAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'channel_id' => 'required|string|max:50',
            'message' => 'required|string|max:2000',
        ]);

        $result = $this->discord->sendMessage($account, $validated['channel_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        return back()->with('error', __('messages.error.discord_failed', ['error' => ($result['error'] ?? 'Unknown')]));
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();

        if ($request->header('X-Signature-Ed25519')) {
            return $this->handleInteraction($request);
        }

        $type = $payload['type'] ?? 0;

        if ($type === 1) {
            return response()->json(['type' => 1]);
        }

        if ($type === 2) {
            return $this->handleSlashCommand($request);
        }

        $message = $payload;
        if (empty($message['content']) || !empty($message['author']['bot'])) {
            return response('ok');
        }

        $channelId = $message['channel_id'] ?? null;
        $text = $message['content'] ?? '';
        $author = $message['author'] ?? [];
        $senderName = $author['username'] ?? 'Discord User';
        $senderId = 'dc:' . ($author['id'] ?? $channelId);

        $account = WaDiscordAccount::where('is_active', true)->first();
        if (!$account) return response('ok');

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => '@' . ($author['username'] ?? $channelId)]
        );

        $mediaUrl = null;
        $messageText = $text;

        if (!empty($message['attachments'])) {
            $attachments = [];
            foreach ($message['attachments'] as $att) {
                $attachments[] = $att['url'] ?? '';
                if (!$mediaUrl) $mediaUrl = $att['url'] ?? null;
            }
            if (empty($text)) {
                $messageText = '[Attachment: ' . implode(', ', $attachments) . ']';
            }
        }

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'discord',
            'channel' => 'discord',
            'message' => $messageText,
            'phone' => $senderId,
            'media_url' => $mediaUrl,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $messageText, $contact->id, $userId);
            } catch (\Throwable) {}
        }

        try {
            $detectedIntent = $this->intent->detect($userId, $messageText, 'discord');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        try {
            $this->teamInbox->autoAssign($contact->id, 0);
        } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $channelId);

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $channelId, $messageText, $defaultAiKey);
            return response('ok');
        }

        if ($this->handleKeywordReply($account, $contact, $channelId, $messageText)) {
            return response('ok');
        }

        $this->handleFallback($account, $contact, $channelId, $messageText);

        return response('ok');
    }

    protected function handleInteraction(Request $request): \Illuminate\Http\JsonResponse
    {
        $payload = $request->all();

        $type = $payload['type'] ?? 0;

        if ($type === 1) {
            return response()->json(['type' => 1]);
        }

        if ($type === 3 && ($payload['data']['component_type'] ?? 0) === 2) {
            return $this->handleButtonInteraction($request);
        }

        if ($type === 5) {
            return $this->handleModalSubmit($request);
        }

        return response()->json(['type' => 4, 'data' => ['content' => 'Interaction received.']]);
    }

    protected function handleSlashCommand(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->input('data', []);
        $commandName = $data['name'] ?? '';
        $options = $data['options'] ?? [];
        $channelId = $request->input('channel_id', '');
        $guildId = $request->input('guild_id', '');
        $userId = $request->input('member.user.id') ?? $request->input('user.id', '');
        $interactionToken = $request->input('token', '');

        $account = WaDiscordAccount::where('is_active', true)->first();
        if (!$account) {
            return response()->json(['type' => 4, 'data' => ['content' => 'Bot not configured.']]);
        }

        $senderId = 'dc:' . $userId;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => 'DC User: ' . $userId, 'display_phone' => 'Discord']
        );

        $optionText = collect($options)->map(fn($o) => $o['value'] ?? '')->filter()->implode(' ');
        $fullText = "/{$commandName} {$optionText}";

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'discord',
            'channel' => 'discord',
            'message' => $fullText,
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        if ($this->handleKeywordReplyInteraction($account, $contact, $channelId, $commandName . ' ' . $optionText, $interactionToken)) {
            return response()->json(['type' => 5]);
        }

        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $reply = $this->ai->send($defaultAiKey, $fullText, $kb ?: null);
                if ($reply) {
                    $this->discord->createReply($account, $interactionToken, $reply);
                    return response()->json(['type' => 5]);
                }
            } catch (\Throwable $e) {
                Log::error('Discord slash command AI failed: ' . $e->getMessage());
            }
        }

        $this->discord->createReply($account, $interactionToken, "Perintah `/{$commandName}` diterima. Tim kami akan segera merespons.");

        return response()->json(['type' => 5]);
    }

    protected function handleButtonInteraction(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->input('data', []);
        $customId = $data['custom_id'] ?? '';
        $userId = $request->input('member.user.id') ?? $request->input('user.id', '');
        $channelId = $request->input('channel_id', '');
        $interactionToken = $request->input('token', '');

        $account = WaDiscordAccount::where('is_active', true)->first();
        if (!$account) {
            return response()->json(['type' => 4, 'data' => ['content' => 'Bot not configured.']]);
        }

        $senderId = 'dc:' . $userId;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => 'DC User: ' . $userId, 'display_phone' => 'Discord']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'discord',
            'channel' => 'discord',
            'message' => "[Button: {$customId}]",
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        if ($this->handleKeywordReplyInteraction($account, $contact, $channelId, $customId, $interactionToken)) {
            return response()->json(['type' => 5]);
        }

        $this->discord->createReply($account, $interactionToken, "Tombol `{$customId}` diterima.");

        return response()->json(['type' => 5]);
    }

    protected function handleModalSubmit(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->input('data', []);
        $customId = $data['custom_id'] ?? '';
        $components = $data['components'] ?? [];
        $userId = $request->input('member.user.id') ?? $request->input('user.id', '');
        $channelId = $request->input('channel_id', '');
        $interactionToken = $request->input('token', '');

        $formValues = [];
        foreach ($components as $comp) {
            foreach ($comp['components'] ?? [] as $field) {
                $formValues[] = ($field['label'] ?? '') . ': ' . ($field['value'] ?? '');
            }
        }
        $formText = implode(', ', $formValues);

        $account = WaDiscordAccount::where('is_active', true)->first();
        if (!$account) {
            return response()->json(['type' => 4, 'data' => ['content' => 'Bot not configured.']]);
        }

        $senderId = 'dc:' . $userId;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => 'DC User: ' . $userId, 'display_phone' => 'Discord']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'discord',
            'channel' => 'discord',
            'message' => "[Modal: {$customId}] {$formText}",
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        $this->discord->createReply($account, $interactionToken, "Form `{$customId}` diterima. Terima kasih!");

        return response()->json(['type' => 5]);
    }

    protected function checkWelcome(WaDiscordAccount $account, WaContact $contact, string $channelId): void
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
            'phone' => $channelId,
        ]);

        $result = $this->discord->sendMessage($account, $channelId, $welcomeText);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'discord',
                'channel' => 'discord',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);

            Log::info("Discord welcome sent", [
                'channel_id' => $channelId,
                'name' => $contact->name,
            ]);
        }
    }

    protected function handleAiAgent(WaDiscordAccount $account, WaContact $contact, string $channelId, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->discord->sendMessage($account, $channelId, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'discord',
                    'channel' => 'discord',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
                ]);

                Log::info("Discord AI agent reply sent", ['channel_id' => $channelId]);
            }
        } catch (\Exception $e) {
            Log::error('Discord AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaDiscordAccount $account, WaContact $contact, string $channelId, string $text): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $channelId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Discord AI auto-reply failed: ' . $e->getMessage());
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $channelId,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $channelId,
            ]);
        }

        $result = $this->discord->sendMessage($account, $channelId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'discord',
            'channel' => 'discord',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("Discord keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'channel_id' => $channelId,
            'ai' => $rule->use_ai,
        ]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleKeywordReplyInteraction(WaDiscordAccount $account, WaContact $contact, string $channelId, string $text, string $interactionToken): bool
    {
        $rule = $this->findAutoReply($account->user_id, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $channelId,
                    ]);
                }
            } catch (\Exception $e) {
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $channelId,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $channelId,
            ]);
        }

        $this->discord->createReply($account, $interactionToken, $replyText);

        return true;
    }

    protected function handleFallback(WaDiscordAccount $account, WaContact $contact, string $channelId, string $text): void
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
            Log::info("Discord fallback cooldown active", ['channel_id' => $channelId]);
            return;
        }

        if ($fallback->use_ai && $fallback->aiKey) {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $replyText = $this->ai->send($fallback->aiKey, $text, $kb ?: null);
        } else {
            $replyText = $this->spintax->process($fallback->reply_message, [
                'name' => $contact->name,
                'phone' => $channelId,
            ]);
        }

        if (!$replyText) return;

        $result = $this->discord->sendMessage($account, $channelId, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'discord',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => ($result['ok'] ?? false) ? 'sent' : 'failed',
        ]);

        Log::info("Discord fallback reply sent", [
            'channel_id' => $channelId,
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
