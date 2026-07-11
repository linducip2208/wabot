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
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    public function __construct(
        protected MetaApiService $meta,
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
        $autoreply = WaAutoreply::where('session_id', $session->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->first(fn($ar) => $ar->matches($text));

        if (!$autoreply) return;

        $reply = $autoreply->reply_message;

        if ($autoreply->use_ai && $autoreply->aiKey) {
            try {
                $reply = app(AiService::class)->send(
                    $autoreply->aiKey,
                    $text
                );
            } catch (\Exception $e) {
                Log::error('AI autoreply failed: ' . $e->getMessage());
            }
        }

        $result = $this->meta->sendText($account, $contact->phone, $reply);

        WaMessage::create([
            'user_id' => $account->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'text',
            'message' => $reply,
            'phone' => $contact->phone,
            'status' => isset($result['messages']) ? 'sent' : 'failed',
        ]);

        $this->meta->markAsRead($account, $waMessage->external_id);
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

    protected function handleFormSubmission(array $message, WaContact $contact): void
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
