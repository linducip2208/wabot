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

    public function sendVideo(WaTelegramAccount $account, string $chatId, string $videoUrl, ?string $caption = null): array
    {
        return $this->call($account, 'sendVideo', array_filter([
            'chat_id' => $chatId,
            'video' => $videoUrl,
            'caption' => $caption,
        ]));
    }

    public function sendAudio(WaTelegramAccount $account, string $chatId, string $audioUrl, ?string $caption = null): array
    {
        return $this->call($account, 'sendAudio', array_filter([
            'chat_id' => $chatId,
            'audio' => $audioUrl,
            'caption' => $caption,
        ]));
    }

    public function sendLocation(WaTelegramAccount $account, string $chatId, float $lat, float $lng): array
    {
        return $this->call($account, 'sendLocation', [
            'chat_id' => $chatId,
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }

    public function sendInlineKeyboard(WaTelegramAccount $account, string $chatId, string $text, array $keyboard): array
    {
        return $this->call($account, 'sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $keyboard]),
        ]);
    }

    public function getFile(WaTelegramAccount $account, string $fileId): ?array
    {
        $result = $this->call($account, 'getFile', ['file_id' => $fileId]);
        return $result['result'] ?? null;
    }

    public function getFileUrl(WaTelegramAccount $account, string $fileId): ?string
    {
        $file = $this->getFile($account, $fileId);
        if (!$file || empty($file['file_path'])) {
            return null;
        }
        return "{$account->baseFileUrl()}/{$file['file_path']}";
    }

    public function call(WaTelegramAccount $account, string $method, array $data = []): array
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
