<?php

namespace App\Services;

use App\Models\WaMetaAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class MetaApiService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    protected function headers(string $accessToken): array
    {
        return [
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function verifyWebhook(WaMetaAccount $account, string $mode, string $token, string $challenge): string|false
    {
        if ($mode === 'subscribe' && $token === $account->webhook_verify_token) {
            return $challenge;
        }
        return false;
    }

    public function sendText(WaMetaAccount $account, string $to, string $message): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => $message],
        ]);
    }

    public function sendImage(WaMetaAccount $account, string $to, string $imageUrl, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'image',
            'image' => ['link' => $imageUrl],
        ];
        if ($caption) {
            $payload['image']['caption'] = $caption;
        }
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, $payload);
    }

    public function sendVideo(WaMetaAccount $account, string $to, string $videoUrl, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'video',
            'video' => ['link' => $videoUrl],
        ];
        if ($caption) {
            $payload['video']['caption'] = $caption;
        }
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, $payload);
    }

    public function sendAudio(WaMetaAccount $account, string $to, string $audioUrl): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'audio',
            'audio' => ['link' => $audioUrl],
        ]);
    }

    public function sendDocument(WaMetaAccount $account, string $to, string $documentUrl, ?string $filename = null, ?string $caption = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'document',
            'document' => ['link' => $documentUrl],
        ];
        if ($filename) {
            $payload['document']['filename'] = $filename;
        }
        if ($caption) {
            $payload['document']['caption'] = $caption;
        }
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, $payload);
    }

    public function sendLocation(WaMetaAccount $account, string $to, float $lat, float $lng, string $name, string $address): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'location',
            'location' => [
                'longitude' => $lng,
                'latitude' => $lat,
                'name' => $name,
                'address' => $address,
            ],
        ]);
    }

    public function sendInteractiveButtons(WaMetaAccount $account, string $to, string $bodyText, array $buttons): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $bodyText],
                'action' => ['buttons' => $buttons],
            ],
        ]);
    }

    public function sendInteractiveList(WaMetaAccount $account, string $to, string $bodyText, string $buttonText, array $sections): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => ['text' => $bodyText],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections,
                ],
            ],
        ]);
    }

    public function sendTemplate(WaMetaAccount $account, string $to, string $templateName, string $language, array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];
        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, $payload);
    }

    public function markAsRead(WaMetaAccount $account, string $messageId): array
    {
        return $this->apiCall('POST', "/{$account->phone_number_id}/messages", $account->access_token, [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ]);
    }

    public function downloadMedia(string $mediaId, string $accessToken): ?string
    {
        try {
            $res = $this->http->get("https://graph.facebook.com/v22.0/{$mediaId}", [
                'headers' => $this->headers($accessToken),
            ]);
            $data = json_decode($res->getBody()->getContents(), true);
            $mediaUrl = $data['url'] ?? null;
            if (!$mediaUrl) return null;

            $res = $this->http->get($mediaUrl, [
                'headers' => $this->headers($accessToken),
            ]);
            return $res->getBody()->getContents();
        } catch (GuzzleException $e) {
            Log::error("MetaApiService::downloadMedia failed: {$e->getMessage()}");
            return null;
        }
    }

    public function getTemplates(WaMetaAccount $account, int $limit = 50): array
    {
        $wabaId = $account->waba_id;
        if (!$wabaId) {
            $wabaId = $this->getWabaId($account);
            if ($wabaId) {
                $account->update(['waba_id' => $wabaId]);
            }
        }

        return $this->apiCall('GET', "/{$wabaId}/message_templates", $account->access_token, [
            'limit' => $limit,
            'status' => 'APPROVED',
        ]);
    }

    public function getWabaId(WaMetaAccount $account): ?string
    {
        $result = $this->apiCall('GET', "/{$account->phone_number_id}", $account->access_token);
        return $result['waba_id'] ?? null;
    }

    public function getBusinessInfo(WaMetaAccount $account): array
    {
        return $this->apiCall('GET', "/{$account->phone_number_id}/whatsapp_business_profile", $account->access_token, [
            'fields' => 'about,address,description,email,profile_picture_url,websites,vertical',
        ]);
    }

    public function verifySignature(string $payload, string $signature, string $appSecret): bool
    {
        $expected = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
        return hash_equals($expected, $signature);
    }

    public function configureWebhook(string $accessToken, string $appId, string $callbackUrl, string $verifyToken): array
    {
        return $this->apiCall('POST', "/{$appId}/subscriptions", $accessToken, [
            'object' => 'whatsapp_business_account',
            'callback_url' => $callbackUrl,
            'verify_token' => $verifyToken,
            'fields' => json_encode(['messages', 'message_template_status_update']),
        ]);
    }

    protected function apiCall(string $method, string $endpoint, string $accessToken, array $data = []): array
    {
        try {
            $options = ['headers' => $this->headers($accessToken)];

            if ($method === 'GET') {
                $options['query'] = $data;
            } else {
                $options['json'] = $data;
            }

            $res = $this->http->request($method, "https://graph.facebook.com/v22.0{$endpoint}", $options);
            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (!empty($body['error'])) {
                Log::error("MetaApiService API error", ['endpoint' => $endpoint, 'error' => $body['error']]);
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error("MetaApiService HTTP error: {$e->getMessage()}", ['endpoint' => $endpoint]);
            return ['error' => $e->getMessage()];
        }
    }
}
