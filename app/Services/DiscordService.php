<?php

namespace App\Services;

use App\Models\WaDiscordAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class DiscordService
{
    protected Client $http;
    protected string $apiBase = 'https://discord.com/api/v10';

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    protected function headers(WaDiscordAccount $account): array
    {
        return [
            'Authorization' => "Bot {$account->bot_token}",
            'Content-Type' => 'application/json',
        ];
    }

    public function getMe(WaDiscordAccount $account): ?array
    {
        try {
            $res = $this->http->get("{$this->apiBase}/users/@me", [
                'headers' => $this->headers($account),
            ]);
            $body = json_decode($res->getBody()->getContents(), true);
            return $body ?? null;
        } catch (GuzzleException $e) {
            Log::error("Discord getMe error: {$e->getMessage()}");
            return null;
        }
    }

    public function sendMessage(WaDiscordAccount $account, string $channelId, string $message): array
    {
        return $this->call($account, 'POST', "channels/{$channelId}/messages", [
            'content' => $message,
        ]);
    }

    public function sendEmbed(WaDiscordAccount $account, string $channelId, array $embed): array
    {
        return $this->call($account, 'POST', "channels/{$channelId}/messages", [
            'embeds' => [$embed],
        ]);
    }

    public function sendDM(WaDiscordAccount $account, string $userId, string $message): array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/users/@me/channels", [
                'headers' => $this->headers($account),
                'json' => ['recipient_id' => $userId],
            ]);
            $channel = json_decode($res->getBody()->getContents(), true);

            if (empty($channel['id'])) {
                return ['ok' => false, 'error' => 'Failed to create DM channel'];
            }

            return $this->sendMessage($account, $channel['id'], $message);
        } catch (GuzzleException $e) {
            Log::error("Discord sendDM error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function createReply(WaDiscordAccount $account, string $interactionToken, string $message): array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/interactions/{$interactionToken}/callback", [
                'headers' => $this->headers($account),
                'json' => [
                    'type' => 4,
                    'data' => ['content' => $message],
                ],
            ]);

            $statusCode = $res->getStatusCode();
            return ['ok' => $statusCode >= 200 && $statusCode < 300];
        } catch (GuzzleException $e) {
            Log::error("Discord interaction reply error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function createFollowup(WaDiscordAccount $account, string $applicationId, string $interactionToken, string $message): array
    {
        try {
            $res = $this->http->post("{$this->apiBase}/webhooks/{$applicationId}/{$interactionToken}", [
                'headers' => $this->headers($account),
                'json' => ['content' => $message],
            ]);

            $body = json_decode($res->getBody()->getContents(), true);
            return array_merge(['ok' => true], $body ?? []);
        } catch (GuzzleException $e) {
            Log::error("Discord followup error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function call(WaDiscordAccount $account, string $method, string $path, array $data = []): array
    {
        try {
            $options = [
                'headers' => $this->headers($account),
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $url = "{$this->apiBase}/{$path}";

            $res = match (strtoupper($method)) {
                'GET' => $this->http->get($url, $options),
                'PATCH' => $this->http->patch($url, $options),
                'DELETE' => $this->http->delete($url, $options),
                default => $this->http->post($url, $options),
            };

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (isset($body['code']) && $body['code'] !== 0) {
                Log::error("Discord API error", ['path' => $path, 'body' => $body]);
                return ['ok' => false, 'error' => $body['message'] ?? 'Unknown error'];
            }

            return array_merge(['ok' => true], $body);
        } catch (GuzzleException $e) {
            Log::error("DiscordService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
