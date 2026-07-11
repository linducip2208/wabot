<?php

namespace App\Services;

use App\Models\WaInstagramAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class InstagramService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function getAuthUrl(WaInstagramAccount $account, string $redirectUri): string
    {
        $base = 'https://api.instagram.com/oauth/authorize';
        $params = http_build_query([
            'client_id' => $account->app_id,
            'redirect_uri' => $redirectUri,
            'scope' => 'instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments',
            'response_type' => 'code',
        ]);

        return "{$base}?{$params}";
    }

    public function exchangeToken(WaInstagramAccount $account, string $code, string $redirectUri): ?array
    {
        try {
            $res = $this->http->post('https://api.instagram.com/oauth/access_token', [
                'form_params' => [
                    'client_id' => $account->app_id,
                    'client_secret' => $account->app_secret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code' => $code,
                ],
            ]);

            $data = json_decode($res->getBody()->getContents(), true);
            return $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Instagram exchangeToken failed: {$e->getMessage()}");
            return null;
        }
    }

    public function getLongLivedToken(WaInstagramAccount $account): ?array
    {
        try {
            $res = $this->http->get('https://graph.instagram.com/access_token', [
                'query' => [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $account->app_secret,
                    'access_token' => $account->access_token,
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return null;
        }
    }

    public function sendDM(string $instagramId, string $accessToken, string $message, ?string $recipientId = null): array
    {
        try {
            $res = $this->http->post("https://graph.facebook.com/v22.0/{$instagramId}/messages", [
                'headers' => ['Authorization' => "Bearer {$accessToken}", 'Content-Type' => 'application/json'],
                'json' => [
                    'recipient' => ['id' => $recipientId ?? $instagramId],
                    'message' => ['text' => $message],
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function sendImage(string $instagramId, string $accessToken, string $imageUrl, ?string $recipientId = null): array
    {
        try {
            $res = $this->http->post("https://graph.facebook.com/v22.0/{$instagramId}/messages", [
                'headers' => ['Authorization' => "Bearer {$accessToken}", 'Content-Type' => 'application/json'],
                'json' => [
                    'recipient' => ['id' => $recipientId ?? $instagramId],
                    'message' => [
                        'attachment' => [
                            'type' => 'image',
                            'payload' => ['url' => $imageUrl],
                        ],
                    ],
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public function sendVideo(string $instagramId, string $accessToken, string $videoUrl, ?string $recipientId = null): array
    {
        try {
            $res = $this->http->post("https://graph.facebook.com/v22.0/{$instagramId}/messages", [
                'headers' => ['Authorization' => "Bearer {$accessToken}", 'Content-Type' => 'application/json'],
                'json' => [
                    'recipient' => ['id' => $recipientId ?? $instagramId],
                    'message' => [
                        'attachment' => [
                            'type' => 'video',
                            'payload' => ['url' => $videoUrl],
                        ],
                    ],
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
