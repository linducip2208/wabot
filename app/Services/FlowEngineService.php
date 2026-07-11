<?php

namespace App\Services;

use App\Models\WaFlow;
use App\Models\WaFlowNode;
use App\Models\WaContact;
use App\Models\WaSession;
use App\Models\WaMessage;
use Illuminate\Support\Facades\Log;

class FlowEngineService
{
    protected AiService $ai;
    protected BaileysService $baileys;
    protected SpintaxService $spintax;

    public function __construct(AiService $ai, BaileysService $baileys, SpintaxService $spintax)
    {
        $this->ai = $ai;
        $this->baileys = $baileys;
        $this->spintax = $spintax;
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

        $result = $this->baileys->send($session->server, $session->session_id, $contact->phone, $message);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $session->user_id,
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'direction' => 'out',
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

        // Send media with optional caption
        $caption = $this->spintax->process($node->reply_message ?: '', [
            'name' => $contact->name, 'phone' => $contact->phone,
        ]);

        $this->baileys->sendMedia($session->server, $session->session_id, $contact->phone, $node->media_url, $caption);
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
        $result = $this->baileys->send($session->server, $session->session_id, $contact->phone, $message);

        if ($result['ok'] ?? false) {
            WaMessage::create([
                'user_id' => $session->user_id,
                'session_id' => $session->id,
                'contact_id' => $contact->id,
                'direction' => 'out',
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
            $result = $this->baileys->send($session->server, $session->session_id, $contact->phone, $reply);
            if ($result['ok'] ?? false) {
                WaMessage::create([
                    'user_id' => $session->user_id,
                    'session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
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
