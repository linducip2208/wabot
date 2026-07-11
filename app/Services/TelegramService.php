<?php

namespace App\Services;

use App\Models\WaTelegramAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function getMe(WaTelegramAccount $account): ?array
    {
        $result = $this->call($account, 'getMe');
        return $result['result'] ?? null;
    }

    public function sendMessage(WaTelegramAccount $account, string $chatId, string $text): array
    {
        return $this->call($account, 'sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ]);
    }

    public function sendPhoto(WaTelegramAccount $account, string $chatId, string $photoUrl, ?string $caption = null): array
    {
        return $this->call($account, 'sendPhoto', array_filter([
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
        ]));
    }

    public function sendDocument(WaTelegramAccount $account, string $chatId, string $documentUrl, ?string $caption = null): array
    {
        return $this->call($account, 'sendDocument', array_filter([
            'chat_id' => $chatId,
            'document' => $documentUrl,
            'caption' => $caption,
        ]));
    }

    public function setWebhook(WaTelegramAccount $account, string $url): array
    {
        return $this->call($account, 'setWebhook', ['url' => $url]);
    }

    public function deleteWebhook(WaTelegramAccount $account): array
    {
        return $this->call($account, 'deleteWebhook');
    }

    public function getUpdates(WaTelegramAccount $account, int $offset = 0, int $limit = 10): array
    {
        return $this->call($account, 'getUpdates', [
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => 5,
        ]);
    }

    protected function call(WaTelegramAccount $account, string $method, array $data = []): array
    {
        try {
            $res = $this->http->post("{$account->baseUrl()}/{$method}", [
                'json' => $data,
            ]);

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (!($body['ok'] ?? false)) {
                Log::error("Telegram API error: {$method}", ['body' => $body]);
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error("TelegramService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
