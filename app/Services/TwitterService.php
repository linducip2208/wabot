<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TwitterService
{
    protected Client $http;
    protected string $apiBase = 'https://api.x.com/2';
    protected string $authBase = 'https://x.com/i/oauth2/authorize';
    protected string $tokenUrl = 'https://api.x.com/2/oauth2/token';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function getAuthUrl(string $clientId, string $redirectUri, string $codeChallenge, string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'dm.read dm.write tweet.read tweet.write users.read offline.access',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        return "{$this->authBase}?{$query}";
    }

    public static function generateCodeVerifier(): string
    {
        $bytes = random_bytes(64);
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function generateCodeChallenge(string $codeVerifier): string
    {
        $hash = hash('sha256', $codeVerifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    public function exchangeToken(string $clientId, string $clientSecret, string $code, string $redirectUri, string $codeVerifier): ?array
    {
        try {
            $res = $this->http->post($this->tokenUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
                ],
                'form_params' => [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $redirectUri,
                    'code_verifier' => $codeVerifier,
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("X/Twitter token exchange error: {$e->getMessage()}");
            return null;
        }
    }

    public function refreshToken(string $clientId, string $clientSecret, string $refreshToken): ?array
    {
        try {
            $res = $this->http->post($this->tokenUrl, [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . base64_encode("{$clientId}:{$clientSecret}"),
                ],
                'form_params' => [
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ],
            ]);

            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("X/Twitter token refresh error: {$e->getMessage()}");
            return null;
        }
    }

    public function getMe(string $accessToken): ?array
    {
        try {
            $res = $this->http->get("{$this->tokenUrl}/me", [
                'headers' => ['Authorization' => "Bearer {$accessToken}"],
            ]);
            return json_decode($res->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error("X/Twitter getMe error: {$e->getMessage()}");
            return null;
        }
    }

    public function sendDM(string $accessToken, string $recipientId, string $text): array
    {
        return $this->call($accessToken, 'POST', '/dm_conversations/with/:participant_id:/messages', [
            'participant_id' => $recipientId,
            'message' => ['text' => $text],
        ], ['participant_id' => $recipientId]);
    }

    public function sendTweet(string $accessToken, string $text): array
    {
        return $this->call($accessToken, 'POST', '/tweets', ['text' => $text]);
    }

    public function getDmConversations(string $accessToken): array
    {
        $result = $this->call($accessToken, 'GET', '/dm_conversations/with/:participant_id:/dm_events', [
            'max_results' => 50,
        ], ['participant_id' => '']);
        return $result;
    }

    public function getUserByUsername(string $accessToken, string $username): ?array
    {
        try {
            $res = $this->http->get("{$this->apiBase}/users/by/username/{$username}", [
                'headers' => ['Authorization' => "Bearer {$accessToken}"],
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return $body['data'] ?? null;
        } catch (GuzzleException $e) {
            Log::error("X/Twitter getUserByUsername error: {$e->getMessage()}");
            return null;
        }
    }

    protected function call(string $accessToken, string $method, string $path, array $data = [], array $pathParams = []): array
    {
        try {
            $url = "{$this->apiBase}{$path}";

            foreach ($pathParams as $key => $value) {
                $url = str_replace(":{$key}:", $value, $url);
            }

            $options = [
                'headers' => [
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type' => 'application/json',
                ],
            ];

            if (strtoupper($method) === 'GET') {
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
                $res = $this->http->get($url, $options);
            } else {
                $options['json'] = $data;
                $res = $this->http->post($url, $options);
            }

            $body = json_decode($res->getBody()->getContents(), true) ?? [];
            $statusCode = $res->getStatusCode();

            if ($statusCode >= 400) {
                Log::error("X/Twitter API error: {$path}", ['status' => $statusCode, 'body' => $body]);
                return ['ok' => false, 'error' => $body['detail'] ?? $body['title'] ?? "HTTP {$statusCode}"];
            }

            return array_merge(['ok' => true], $body);
        } catch (GuzzleException $e) {
            Log::error("TwitterService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
