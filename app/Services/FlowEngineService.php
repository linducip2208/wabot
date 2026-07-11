<?php

namespace App\Services;

use App\Models\WaFlow;
use App\Models\WaFlowNode;
use App\Models\WaContact;
use App\Models\WaInstagramAccount;
use App\Models\WaMetaAccount;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use Illuminate\Support\Facades\Log;

class FlowEngineService
{
    protected AiService $ai;
    protected BaileysService $baileys;
    protected InstagramService $instagram;
    protected TelegramService $telegram;
    protected MetaApiService $metaApi;
    protected SpintaxService $spintax;

    public function __construct(
        AiService $ai,
        BaileysService $baileys,
        InstagramService $instagram,
        TelegramService $telegram,
        MetaApiService $metaApi,
        SpintaxService $spintax,
    ) {
        $this->ai = $ai;
        $this->baileys = $baileys;
        $this->instagram = $instagram;
        $this->telegram = $telegram;
        $this->metaApi = $metaApi;
        $this->spintax = $spintax;
    }

    protected function detectChannel(WaContact $contact): string
    {
        if (str_starts_with($contact->phone, 'ig:')) return 'instagram';
        if (str_starts_with($contact->phone, 'tg:')) return 'telegram';
        return 'baileys';
    }

    protected function sendViaChannel(WaSession $session, WaContact $contact, string $message, ?string $forcedChannel = null): array
    {
        $channel = $forcedChannel ?: $this->detectChannel($contact);

        switch ($channel) {
            case 'instagram':
                $account = WaInstagramAccount::where('user_id', $session->user_id)
                    ->where('is_active', true)->first();
                if (!$account) return ['ok' => false, 'error' => 'No active Instagram account'];
                $recipientId = str_replace('ig:', '', $contact->phone);
                $result = $this->instagram->sendDM($account->instagram_id, $account->access_token, $message, $recipientId);
                return ['ok' => empty($result['error']), 'error' => $result['error'] ?? null];

            case 'telegram':
                $account = WaTelegramAccount::where('user_id', $session->user_id)
                    ->where('is_active', true)->first();
                if (!$account) return ['ok' => false, 'error' => 'No active Telegram account'];
                $chatId = str_replace('tg:', '', $contact->phone);
                $result = $this->telegram->sendMessage($account, $chatId, $message);
                return ['ok' => $result['ok'] ?? false, 'error' => $result['description'] ?? null];

            default:
                if ($session->meta_account_id) {
                    $metaAccount = WaMetaAccount::find($session->meta_account_id);
                    if ($metaAccount && $metaAccount->is_active) {
                        $result = $this->metaApi->sendText($metaAccount, $contact->phone, $message);
                        return ['ok' => empty($result['error']), 'error' => $result['error'] ?? null];
                    }
                }
                return $this->baileys->send($session->server, $session->session_id, $contact->phone, $message);
        }
    }

    /**
     * Proses auto-reply berbasis flow.
     */
    public function execute(WaFlow $flow, WaSession $session, WaContact $contact, string $incomingMessage): bool
    {
        $triggerNode = $flow->nodes()->where('type', 'trigger')->first();
        if (!$triggerNode) return false;

        $currentNode = $triggerNode->nextNodeTrue ?? $flow->nodes()->where('sort_order', '>', $triggerNode->sort_order)->first();
        if (!$currentNode) return false;

        return $this->processNode($currentNode, $session, $contact, $incomingMessage);
    }

    protected function processNode(WaFlowNode $node, WaSession $session, WaContact $contact, string $context): bool
    {
        Log::info("FlowEngine: processing node", [
            'flow_id' => $node->flow_id,
            'node_id' => $node->id,
            'type' => $node->type,
            'contact' => $contact->phone,
        ]);

        switch ($node->type) {
            case 'message':
                return $this->handleMessage($node, $session, $contact, $context);
            case 'image':
                return $this->handleMedia($node, $session, $contact);
            case 'button':
                return $this->handleButton($node, $session, $contact);
            case 'ai':
                return $this->handleAi($node, $session, $contact, $context);
            case 'wait':
                return $this->handleWait($node);
            case 'condition':
                return $this->handleCondition($node, $context);
            default:
                return true;
        }
    }

    protected function handleMessage(WaFlowNode $node, WaSession $session, WaContact $contact, string $context): bool
    {
        $message = $this->spintax->process($node->reply_message, [
            'name' => $contact->name,
            'phone' => $contact->phone,
        ]);

        $channel = $node->channel ?: $this->detectChannel($contact);
        $result = $this->sendViaChannel($session, $contact, $message, $node->channel);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $session->user_id,
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'channel' => $channel,
                'message' => $message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);
        }

        $this->goNext($node, true);
        return true;
    }

    protected function handleMedia(WaFlowNode $node, WaSession $session, WaContact $contact): bool
    {
        if (!$node->media_url) return $this->goNext($node, true);

        $caption = $this->spintax->process($node->reply_message ?: '', [
            'name' => $contact->name, 'phone' => $contact->phone,
        ]);

        $channel = $this->detectChannel($contact);

        switch ($channel) {
            case 'instagram':
                $account = WaInstagramAccount::where('user_id', $session->user_id)
                    ->where('is_active', true)->first();
                if ($account) {
                    $recipientId = str_replace('ig:', '', $contact->phone);
                    $this->instagram->sendImage($account->instagram_id, $account->access_token, $node->media_url, $recipientId);
                }
                break;
            case 'telegram':
                $account = WaTelegramAccount::where('user_id', $session->user_id)
                    ->where('is_active', true)->first();
                if ($account) {
                    $chatId = str_replace('tg:', '', $contact->phone);
                    $this->telegram->sendPhoto($account, $chatId, $node->media_url, $caption);
                }
                break;
            default:
                $this->baileys->sendMedia($session->server, $session->session_id, $contact->phone, $node->media_url, $caption);
        }

        $this->goNext($node, true);
        return true;
    }

    protected function handleButton(WaFlowNode $node, WaSession $session, WaContact $contact): bool
    {
        $config = $node->config ?? [];
        $buttons = $config['buttons'] ?? [];
        $bodyText = $node->reply_message ?: ($config['body_text'] ?? __('messages.flows.choose_one'));

        if (empty($buttons)) return $this->goNext($node, true);

        $buttonList = [];
        foreach ($buttons as $i => $btn) {
            $buttonList[] = ($i + 1) . '. ' . ($btn['text'] ?? $btn);
        }

        $message = $bodyText . "\n\n" . implode("\n", $buttonList);
        $channel = $this->detectChannel($contact);
        $result = $this->sendViaChannel($session, $contact, $message);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $session->user_id,
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'channel' => $channel,
                'message' => $message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);
        }

        $this->goNext($node, true);
        return true;
    }

    protected function handleAi(WaFlowNode $node, WaSession $session, WaContact $contact, string $context): bool
    {
        if (!$node->aiKey) return $this->goNext($node, true);

        $aiService = app(AiService::class);
        $kb = $aiService->getKnowledgeContext($session->user_id);
        $reply = $aiService->send($node->aiKey, $context, $kb ?: null);

        if ($reply) {
            $channel = $node->channel ?: $this->detectChannel($contact);
            $result = $this->sendViaChannel($session, $contact, $reply, $node->channel);
            if ($result['ok'] ?? false) {
                WaMessage::create([
                    'user_id' => $session->user_id,
                    'session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'channel' => $channel,
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => 'sent',
                ]);
            }
        }

        $this->goNext($node, true);
        return true;
    }

    protected function handleWait(WaFlowNode $node): bool
    {
        // Wait node — handled by scheduling. Return true to continue flow.
        return true;
    }

    protected function handleCondition(WaFlowNode $node, string $context): bool
    {
        $field = $node->condition_field;
        $operator = $node->condition_operator;
        $value = mb_strtolower($node->condition_value ?? '');
        $incoming = mb_strtolower($context);

        $matched = match ($operator) {
            'equals' => $incoming === $value,
            'contains' => str_contains($incoming, $value),
            'not_contains' => !str_contains($incoming, $value),
            default => false,
        };

        $this->goNext($node, $matched);
        return $matched;
    }

    protected function goNext(WaFlowNode $node, bool $trueBranch): bool
    {
        $next = $trueBranch ? $node->nextNodeTrue : $node->nextNodeFalse;
        if (!$next) return true;
        return $this->processNode($next, session('flow_session'), session('flow_contact'), '');
    }
}
