<?php

namespace App\Services;

use App\Models\WaLineAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class LineService
{
    protected Client $http;
    protected string $apiBase = 'https://api.line.me/v2';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    protected function headers(WaLineAccount $account): array
    {
        return [
            'Authorization' => "Bearer {$account->channel_access_token}",
            'Content-Type' => 'application/json',
        ];
    }

    public function pushMessage(WaLineAccount $account, string $userId, string $text): array
    {
        return $this->call($account, 'POST', '/bot/message/push', [
            'to' => $userId,
            'messages' => [['type' => 'text', 'text' => $text]],
        ]);
    }

    public function replyMessage(WaLineAccount $account, string $replyToken, string $text): array
    {
        return $this->call($account, 'POST', '/bot/message/reply', [
            'replyToken' => $replyToken,
            'messages' => [['type' => 'text', 'text' => $text]],
        ]);
    }

    public function sendFlexMessage(WaLineAccount $account, string $userId, string $altText, array $flexContent): array
    {
        return $this->call($account, 'POST', '/bot/message/push', [
            'to' => $userId,
            'messages' => [[
                'type' => 'flex',
                'altText' => $altText,
                'contents' => $flexContent,
            ]],
        ]);
    }

    public function sendImage(WaLineAccount $account, string $userId, string $imageUrl, ?string $previewUrl = null): array
    {
        return $this->call($account, 'POST', '/bot/message/push', [
            'to' => $userId,
            'messages' => [[
                'type' => 'image',
                'originalContentUrl' => $imageUrl,
                'previewImageUrl' => $previewUrl ?: $imageUrl,
            ]],
        ]);
    }

    public function sendVideo(WaLineAccount $account, string $userId, string $videoUrl, string $previewUrl): array
    {
        return $this->call($account, 'POST', '/bot/message/push', [
            'to' => $userId,
            'messages' => [[
                'type' => 'video',
                'originalContentUrl' => $videoUrl,
                'previewImageUrl' => $previewUrl,
            ]],
        ]);
    }

    public function sendAudio(WaLineAccount $account, string $userId, string $audioUrl, int $durationMs): array
    {
        return $this->call($account, 'POST', '/bot/message/push', [
            'to' => $userId,
            'messages' => [[
                'type' => 'audio',
                'originalContentUrl' => $audioUrl,
                'duration' => $durationMs,
            ]],
        ]);
    }

    public function getProfile(WaLineAccount $account, string $userId): ?array
    {
        try {
            $res = $this->http->get("{$this->apiBase}/bot/profile/{$userId}", [
                'headers' => $this->headers($account),
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return $body;
        } catch (GuzzleException $e) {
            Log::error("LINE getProfile error: {$e->getMessage()}");
            return null;
        }
    }

    public function getRichMenuList(WaLineAccount $account): array
    {
        try {
            $res = $this->http->get("{$this->apiBase}/bot/richmenu/list", [
                'headers' => $this->headers($account),
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return ['ok' => true, 'richmenus' => $body['richmenus'] ?? []];
        } catch (GuzzleException $e) {
            Log::error("LINE getRichMenuList error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function setDefaultRichMenu(WaLineAccount $account, string $richMenuId): array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/bot/user/all/richmenu/{$richMenuId}", [
                'headers' => $this->headers($account),
            ]);
            return ['ok' => $res->getStatusCode() < 300];
        } catch (GuzzleException $e) {
            Log::error("LINE setDefaultRichMenu error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteRichMenu(WaLineAccount $account, string $richMenuId): array
    {
        try {
            $res = $this->http->delete("{$this->apiBase}/bot/richmenu/{$richMenuId}", [
                'headers' => $this->headers($account),
            ]);
            return ['ok' => $res->getStatusCode() < 300];
        } catch (GuzzleException $e) {
            Log::error("LINE deleteRichMenu error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyWebhook(WaLineAccount $account): array
    {
        try {
            $res = $this->http->get("{$this->apiBase}/bot/info", [
                'headers' => $this->headers($account),
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return ['ok' => true, 'data' => $body];
        } catch (GuzzleException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function call(WaLineAccount $account, string $method, string $path, array $data = []): array
    {
        try {
            $options = ['headers' => $this->headers($account)];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $url = "{$this->apiBase}{$path}";

            $res = match (strtoupper($method)) {
                'GET' => $this->http->get($url, $options),
                'DELETE' => $this->http->delete($url, $options),
                default => $this->http->post($url, $options),
            };

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (isset($body['message']) && ($res->getStatusCode() >= 400 || !empty($body['details']))) {
                Log::error("LINE API error", ['path' => $path, 'body' => $body]);
                return ['ok' => false, 'error' => $body['message'] ?? 'Unknown error'];
            }

            return array_merge(['ok' => true], $body);
        } catch (GuzzleException $e) {
            Log::error("LineService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Validate LINE webhook signature.
     */
    public function validateSignature(WaLineAccount $account, string $body, string $signature): bool
    {
        if (!$account->channel_secret) return false;
        $hash = base64_encode(hash_hmac('sha256', $body, $account->channel_secret, true));
        return hash_equals($hash, $signature);
    }
}
