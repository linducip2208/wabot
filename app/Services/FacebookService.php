<?php

namespace App\Services;

use App\Models\WaFacebookAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    protected Client $http;
    protected const GRAPH_VERSION = 'v22.0';
    protected const BASE_URL = 'https://graph.facebook.com';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function sendMessage(WaFacebookAccount $account, string $recipientId, string $text): array
    {
        return $this->post("{$account->page_id}/messages", $account->page_token, [
            'recipient' => ['id' => $recipientId],
            'message' => ['text' => $text],
        ]);
    }

    public function sendImage(WaFacebookAccount $account, string $recipientId, string $imageUrl): array
    {
        return $this->post("{$account->page_id}/messages", $account->page_token, [
            'recipient' => ['id' => $recipientId],
            'message' => [
                'attachment' => [
                    'type' => 'image',
                    'payload' => ['url' => $imageUrl],
                ],
            ],
        ]);
    }

    public function sendVideo(WaFacebookAccount $account, string $recipientId, string $videoUrl): array
    {
        return $this->post("{$account->page_id}/messages", $account->page_token, [
            'recipient' => ['id' => $recipientId],
            'message' => [
                'attachment' => [
                    'type' => 'video',
                    'payload' => ['url' => $videoUrl],
                ],
            ],
        ]);
    }

    public function getUserProfile(string $userId, string $pageToken): ?array
    {
        try {
            $res = $this->http->get(self::BASE_URL . '/' . self::GRAPH_VERSION . "/{$userId}", [
                'query' => [
                    'fields' => 'id,name,profile_pic',
                    'access_token' => $pageToken,
                ],
            ]);

            $data = json_decode($res->getBody()->getContents(), true);
            return $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Facebook getUserProfile failed: {$e->getMessage()}");
            return null;
        }
    }

    public function getPageInfo(string $pageId, string $pageToken): ?array
    {
        try {
            $res = $this->http->get(self::BASE_URL . '/' . self::GRAPH_VERSION . "/{$pageId}", [
                'query' => [
                    'fields' => 'id,name,access_token',
                    'access_token' => $pageToken,
                ],
            ]);

            $data = json_decode($res->getBody()->getContents(), true);
            return $data ?? null;
        } catch (GuzzleException $e) {
            Log::error("Facebook getPageInfo failed: {$e->getMessage()}");
            return null;
        }
    }

    public function replyToComment(string $commentId, string $pageToken, string $message): array
    {
        return $this->post("{$commentId}/replies", $pageToken, [
            'message' => $message,
        ]);
    }

    public function sendPrivateReply(string $commentId, string $pageToken, string $message): array
    {
        return $this->post("{$commentId}/private_replies", $pageToken, [
            'message' => $message,
        ]);
    }

    protected function post(string $endpoint, string $pageToken, array $json): array
    {
        try {
            $res = $this->http->post(
                self::BASE_URL . '/' . self::GRAPH_VERSION . '/' . $endpoint,
                [
                    'headers' => [
                        'Authorization' => "Bearer {$pageToken}",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $json,
                ]
            );

            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            Log::error("Facebook Graph API failed [{$endpoint}]: {$e->getMessage()}");
            return ['error' => $e->getMessage()];
        }
    }
}
