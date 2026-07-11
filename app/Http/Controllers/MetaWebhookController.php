<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaForm;
use App\Models\WaFormSubmission;
use App\Models\WaMessage;
use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\MetaApiService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function __construct(
        protected MetaApiService $meta,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function receive(Request $request)
    {
        if ($request->method() === 'GET') {
            return $this->verify($request);
        }

        Log::info('Meta webhook received', ['body' => $request->all()]);

        $body = $request->all();

        if (empty($body['entry'])) {
            return response()->json(['status' => 'no_entry'], 200);
        }

        foreach ($body['entry'] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                if (($value['messaging_product'] ?? '') !== 'whatsapp') {
                    continue;
                }

                $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;
                $account = WaMetaAccount::where('phone_number_id', $phoneNumberId)->first();
                if (!$account) continue;

                foreach ($value['messages'] ?? [] as $message) {
                    $this->handleMessage($account, $message);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    $this->handleStatus($status);
                }
            }
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function verify(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        if (!$mode || !$token) {
            return response('Missing params', 400);
        }

        $accounts = WaMetaAccount::whereNotNull('webhook_verify_token')->get();

        foreach ($accounts as $account) {
            $result = $this->meta->verifyWebhook($account, $mode, $token, $challenge);
            if ($result !== false) {
                return response($result, 200)->header('Content-Type', 'text/plain');
            }
        }

        return response('Verification failed', 403);
    }

    protected function handleMessage(WaMetaAccount $account, array $message): void
    {
        $phone = $message['from'] ?? null;
        $msgId = $message['id'] ?? null;

        if (!$phone) return;

        $session = WaSession::where('meta_account_id', $account->id)
            ->where('is_active', true)
            ->first();

        if (!$session) return;

        $displayPhone = preg_replace('/[^0-9]/', '', $phone);
        $contactName = $message['profile']['name'] ?? $displayPhone;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $phone],
            ['name' => $contactName, 'display_phone' => $displayPhone]
        );

        $msgType = $message['type'] ?? 'text';
        $msgBody = $this->extractMessageBody($message);

        $waMessage = WaMessage::create([
            'user_id' => $account->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => $msgType,
            'channel' => 'meta',
            'message' => $msgBody,
            'phone' => $phone,
            'status' => 'delivered',
            'external_id' => $msgId,
        ]);

        if ($msgType === 'text' && !empty($msgBody)) {
            $this->processAutoreply($account, $session, $contact, $waMessage, $msgBody);
        }

        if ($msgType === 'interactive') {
            $this->handleFormSubmission($account, $message, $contact);
        }
    }

    protected function extractMessageBody(array $message): string
    {
        $type = $message['type'] ?? 'unknown';

        return match ($type) {
            'text' => $message['text']['body'] ?? '',
            'image' => '[Gambar] ' . ($message['image']['caption'] ?? ''),
            'video' => '[Video] ' . ($message['video']['caption'] ?? ''),
            'audio' => '[Audio]',
            'document' => '[Dokumen] ' . ($message['document']['filename'] ?? ''),
            'location' => '[Lokasi] ' . ($message['location']['latitude'] ?? '') . ',' . ($message['location']['longitude'] ?? ''),
            'sticker' => '[Stiker]',
            'interactive' => $this->extractInteractiveBody($message),
            'reaction' => $message['reaction']['emoji'] ?? '[Reaction]',
            'order' => '[Pesanan]',
            'contacts' => '[Kontak]',
            'button' => $message['button']['text'] ?? '[Button]',
            default => "[{$type}]",
        };
    }

    protected function extractInteractiveBody(array $message): string
    {
        $interactive = $message['interactive'] ?? [];
        $interactiveType = $interactive['type'] ?? '';

        return match ($interactiveType) {
            'button_reply' => $interactive['button_reply']['title'] ?? '[Button Reply]',
            'list_reply' => $interactive['list_reply']['title'] ?? '[List Reply]',
            'nfm_reply' => is_array($interactive['nfm_reply'] ?? null) ? json_encode($interactive['nfm_reply']) : '[Form Reply]',
            default => '[Interactive]',
        };
    }

    protected function processAutoreply(WaMetaAccount $account, WaSession $session, WaContact $contact, WaMessage $waMessage, string $text): void
    {
        $userId = $account->user_id;

        // ── Sentiment analysis ───────────────────────────────────
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $text, $contact->id, $userId, $waMessage->id);
            } catch (\Throwable) {}
        }

        // ── Intent detection ─────────────────────────────────────
        try {
            $detectedIntent = $this->intent->detect($userId, $text, 'meta');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        // ── SLA tracking ─────────────────────────────────────────
        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        // ── Team inbox assignment ────────────────────────────────
        try {
            $this->teamInbox->autoAssign($contact->id, $session->id);
        } catch (\Throwable) {}

        // ── Welcome message ──────────────────────────────────────
        $this->checkWelcome($account, $session, $contact);

        // ── AI agent trigger (intent detection) ──────────────────
        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $text, $defaultAiKey, $waMessage);
            return;
        }

        // ── Keyword auto-reply ───────────────────────────────────
        if ($this->handleKeywordReply($account, $session, $contact, $text, $waMessage)) {
            return;
        }

        // ── Fallback ─────────────────────────────────────────────
        $this->handleFallback($account, $session, $contact, $text);
    }

    protected function checkWelcome(WaMetaAccount $account, WaSession $session, WaContact $contact): void
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
            ->where(function ($query) use ($session) {
                $query->where('session_id', $session->id)
                    ->orWhereNull('session_id');
            })
            ->first();

        if (!$welcomeRule) return;

        $welcomeText = $this->spintax->process($welcomeRule->reply_message, [
            'name' => $contact->name,
            'phone' => $contact->phone,
        ]);

        $result = $this->meta->sendText($account, $contact->phone, $welcomeText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'text',
            'channel' => 'meta',
            'message' => $welcomeRule->reply_message,
            'phone' => $contact->phone,
            'status' => isset($result['messages']) ? 'sent' : 'failed',
        ]);

        Log::info("Meta welcome sent", [
            'phone' => $contact->phone,
            'name' => $contact->name,
        ]);
    }

    protected function handleAiAgent(WaMetaAccount $account, WaContact $contact, string $text, \App\Models\WaAiKey $aiKey, WaMessage $waMessage): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->meta->sendText($account, $contact->phone, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'text',
                    'channel' => 'meta',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => isset($result['messages']) ? 'sent' : 'failed',
                ]);

                try {
                    $this->sla->recordResponse($account->user_id, $contact->id);
                } catch (\Throwable) {}

                Log::info("Meta AI agent reply sent", ['phone' => $contact->phone]);
            }
        } catch (\Exception $e) {
            Log::error('Meta AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaMetaAccount $account, WaSession $session, WaContact $contact, string $text, WaMessage $waMessage): bool
    {
        $rule = $this->findAutoReply($session, $text);

        if (!$rule) return false;

        if ($rule->use_ai && $rule->aiKey) {
            try {
                $kb = $this->ai->getKnowledgeContext($account->user_id);
                $replyText = $this->ai->send($rule->aiKey, $text, $kb ?: null);
                if (!$replyText) {
                    $replyText = $this->spintax->process($rule->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $contact->phone,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Meta AI auto-reply failed: ' . $e->getMessage());
                $replyText = $this->spintax->process($rule->reply_message, [
                    'name' => $contact->name, 'phone' => $contact->phone,
                ]);
            }
        } else {
            $replyText = $this->spintax->process($rule->reply_message, [
                'name' => $contact->name,
                'phone' => $contact->phone,
            ]);
        }

        $result = $this->meta->sendText($account, $contact->phone, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'text',
            'channel' => 'meta',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => isset($result['messages']) ? 'sent' : 'failed',
        ]);

        Log::info("Meta keyword auto-reply sent", [
            'keyword' => $rule->keyword,
            'phone' => $contact->phone,
            'ai' => $rule->use_ai,
        ]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        $this->meta->markAsRead($account, $waMessage->external_id);

        return true;
    }

    protected function handleFallback(WaMetaAccount $account, WaSession $session, WaContact $contact, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->where(function ($query) use ($session) {
                $query->where('session_id', $session->id)
                    ->orWhereNull('session_id');
            })
            ->orderByRaw('session_id IS NULL')
            ->first();

        if (!$fallback) return;

        $recentFallbacks = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->where('type', 'fallback')
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentFallbacks >= 3) {
            Log::info("Meta fallback cooldown active", ['phone' => $contact->phone]);
            return;
        }

        if ($fallback->use_ai && $fallback->aiKey) {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $replyText = $this->ai->send($fallback->aiKey, $text, $kb ?: null);
        } else {
            $replyText = $this->spintax->process($fallback->reply_message, [
                'name' => $contact->name,
                'phone' => $contact->phone,
            ]);
        }

        if (!$replyText) return;

        $result = $this->meta->sendText($account, $contact->phone, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'meta',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => isset($result['messages']) ? 'sent' : 'failed',
        ]);

        Log::info("Meta fallback reply sent", [
            'phone' => $contact->phone,
            'ai' => (bool) $fallback->use_ai,
        ]);
    }

    protected function findAutoReply(WaSession $session, string $incomingMessage): ?WaAutoreply
    {
        $rules = WaAutoreply::where('user_id', $session->user_id)
            ->where('is_active', true)
            ->whereNotIn('match_type', ['welcome', 'fallback'])
            ->where(function ($query) use ($session) {
                $query->where('session_id', $session->id)
                    ->orWhereNull('session_id');
            })
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($incomingMessage)) {
                return $rule;
            }
        }

        return null;
    }

    protected function handleStatus(array $status): void
    {
        $msgId = $status['id'] ?? null;
        $statusType = $status['status'] ?? null;

        if (!$msgId || !$statusType) return;

        $mappedStatus = match ($statusType) {
            'sent' => 'sent',
            'delivered' => 'delivered',
            'read' => 'read',
            'failed' => 'failed',
            'deleted' => 'failed',
            default => null,
        };

        if (!$mappedStatus) return;

        WaMessage::where('external_id', $msgId)
            ->update(['status' => $mappedStatus]);
    }

    protected function handleFormSubmission(WaMetaAccount $account, array $message, WaContact $contact): void
    {
        $interactive = $message['interactive'] ?? [];
        if (($interactive['type'] ?? '') !== 'nfm_reply') return;

        $responseJson = $interactive['nfm_reply']['response_json'] ?? null;
        if (!$responseJson) return;

        $data = is_string($responseJson) ? json_decode($responseJson, true) : $responseJson;
        if (!$data || !is_array($data)) return;

        $phone = preg_replace('/[^0-9]/', '', $contact->phone);

        WaFormSubmission::create([
            'form_id' => 0,
            'contact_id' => $contact->id,
            'phone' => $phone,
            'data' => $data,
            'message_id' => $message['id'] ?? null,
            'status' => 'new',
        ]);
    }
}
