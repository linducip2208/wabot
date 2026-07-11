<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TikTokService
{
    protected Client $http;
    protected string $apiBase = 'https://open.tiktokapis.com/v2';
    protected string $authBase = 'https://www.tiktok.com/v2';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function getAuthUrl(string $clientKey, string $redirectUri, string $state): string
    {
        $query = http_build_query([
            'client_key' => $clientKey,
            'response_type' => 'code',
            'scope' => 'user.info.basic,business.direct.message',
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        return "{$this->authBase}/auth/authorize/?{$query}";
    }

    public function exchangeToken(string $clientKey, string $clientSecret, string $code, string $redirectUri): ?array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/oauth/token/", [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'client_key' => $clientKey,
                    'client_secret' => $clientSecret,
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("TikTok token exchange error: {$e->getMessage()}");
            return null;
        }
    }

    public function refreshToken(string $clientKey, string $clientSecret, string $refreshToken): ?array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/oauth/token/", [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'client_key' => $clientKey,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("TikTok token refresh error: {$e->getMessage()}");
            return null;
        }
    }

    public function sendMessage(string $accessToken, string $openId, string $text): array
    {
        return $this->call($accessToken, 'POST', '/business/direct/message/send/', [
            'open_id' => $openId,
            'message' => [
                'type' => 'text',
                'text' => $text,
            ],
        ]);
    }

    public function sendImage(string $accessToken, string $openId, string $mediaId): array
    {
        return $this->call($accessToken, 'POST', '/business/direct/message/send/', [
            'open_id' => $openId,
            'message' => [
                'type' => 'image',
                'media_id' => $mediaId,
            ],
        ]);
    }

    public function sendVideo(string $accessToken, string $openId, string $mediaId): array
    {
        return $this->call($accessToken, 'POST', '/business/direct/message/send/', [
            'open_id' => $openId,
            'message' => [
                'type' => 'video',
                'media_id' => $mediaId,
            ],
        ]);
    }

    public function getUserInfo(string $accessToken, string $openId): ?array
    {
        $result = $this->call($accessToken, 'GET', '/user/info/', [
            'open_id' => $openId,
        ]);
        return $result['data'] ?? null;
    }

    protected function call(string $accessToken, string $method, string $path, array $data = []): array
    {
        try {
            $options = [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
            ];

            $url = "{$this->apiBase}{$path}";

            if (strtoupper($method) === 'GET') {
                $url .= '?' . http_build_query($data);
                $res = $this->http->get($url, $options);
            } else {
                $options['json'] = $data;
                $res = $this->http->post($url, $options);
            }

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (($body['error'] ?? '') !== 'ok' && isset($body['error'])) {
                Log::error("TikTok API error: {$path}", ['body' => $body]);
                return ['ok' => false, 'error' => $body['error'] ?? $body['message'] ?? 'Unknown error'];
            }

            return array_merge(['ok' => true], $body);
        } catch (GuzzleException $e) {
            Log::error("TikTokService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
