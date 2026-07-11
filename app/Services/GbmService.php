<?php

namespace App\Services;

use App\Models\WaGbmAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GbmService
{
    protected Client $http;
    protected string $tokenEndpoint = 'https://oauth2.googleapis.com/token';
    protected string $apiBase = 'https://businessmessages.googleapis.com/v1';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    protected function getAccessToken(WaGbmAccount $account): ?string
    {
        try {
            $keyFile = json_decode($account->service_account_json, true);
            if (!$keyFile) return null;

            $jwt = $this->createJwt($keyFile);
            $res = $this->http->post($this->tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt,
                ],
            ]);

            $data = json_decode($res->getBody()->getContents(), true);
            return $data['access_token'] ?? null;
        } catch (GuzzleException $e) {
            Log::error("GBM auth error: {$e->getMessage()}");
            return null;
        }
    }

    protected function createJwt(array $keyFile): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

        $now = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $keyFile['client_email'],
            'scope' => 'https://www.googleapis.com/auth/businessmessages',
            'aud' => $this->tokenEndpoint,
            'exp' => $now + 3600,
            'iat' => $now,
        ]));

        $signatureInput = "{$header}.{$payload}";
        openssl_sign($signatureInput, $signature, $keyFile['private_key'], 'SHA256');
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "{$signatureInput}.{$signatureEncoded}";
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function sendMessage(WaGbmAccount $account, string $conversationId, string $message): array
    {
        return $this->call($account, "conversations/{$conversationId}/messages", [
            'messageId' => uniqid('gbm-msg-', true),
            'text' => $message,
            'representative' => [
                'representativeType' => 'BOT',
                'displayName' => $account->name,
            ],
            'fallback' => 'LIVE',
        ]);
    }

    public function sendRichCard(WaGbmAccount $account, string $conversationId, array $cardData): array
    {
        return $this->call($account, "conversations/{$conversationId}/messages", array_merge([
            'messageId' => uniqid('gbm-card-', true),
            'representative' => [
                'representativeType' => 'BOT',
                'displayName' => $account->name,
            ],
            'fallback' => 'LIVE',
        ], $cardData));
    }

    public function sendSuggestion(WaGbmAccount $account, string $conversationId, array $suggestions): array
    {
        return $this->call($account, "conversations/{$conversationId}/messages", [
            'messageId' => uniqid('gbm-sug-', true),
            'text' => 'Choose an option:',
            'suggestions' => $suggestions,
            'representative' => [
                'representativeType' => 'BOT',
                'displayName' => $account->name,
            ],
            'fallback' => 'LIVE',
        ]);
    }

    public function call(WaGbmAccount $account, string $path, array $data = []): array
    {
        $token = $this->getAccessToken($account);

        if (!$token) {
            return ['ok' => false, 'error' => 'Failed to get GBM access token'];
        }

        try {
            $res = $this->http->post("{$this->apiBase}/{$path}", [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $data,
            ]);

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (isset($body['error'])) {
                Log::error("GBM API error", ['path' => $path, 'body' => $body]);
                return ['ok' => false, 'error' => $body['error']['message'] ?? 'Unknown error'];
            }

            return array_merge(['ok' => true], $body);
        } catch (GuzzleException $e) {
            Log::error("GBMService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
