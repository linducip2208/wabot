<?php

namespace App\Services;

use App\Models\WaServer;
use App\Models\WaSession;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class BaileysService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function check(WaServer $server): bool
    {
        try {
            $res = $this->http->get($server->baseUrl() . '/', [
                'headers' => $this->headers($server),
                'timeout' => 5,
            ]);
            return $res->getStatusCode() === 200;
        } catch (GuzzleException) {
            return false;
        }
    }

    public function createSession(WaServer $server, WaSession $session, string $webhookUrl): array
    {
        try {
            $res = $this->http->post($server->baseUrl() . '/sessions/create', [
                'headers' => $this->headers($server),
                'json' => [
                    'session_id' => $session->session_id,
                    'webhook_url' => $webhookUrl,
                ],
                'timeout' => 15,
            ]);
            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['ok' => false, 'status' => 'error', 'qr' => null, 'message' => $e->getMessage()];
        }
    }

    public function getStatus(WaServer $server, string $sessionId): array
    {
        try {
            $res = $this->http->get($server->baseUrl() . "/sessions/{$sessionId}/status", [
                'headers' => $this->headers($server),
            ]);
            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException) {
            return ['status' => 'error'];
        }
    }

    public function getQr(WaServer $server, string $sessionId): ?string
    {
        try {
            $res = $this->http->get($server->baseUrl() . "/sessions/{$sessionId}/qr", [
                'headers' => $this->headers($server),
            ]);
            $data = json_decode($res->getBody()->getContents(), true) ?? [];
            return $data['qr'] ?? null;
        } catch (GuzzleException) {
            return null;
        }
    }

    public function deleteSession(WaServer $server, string $sessionId): bool
    {
        try {
            $res = $this->http->delete($server->baseUrl() . "/sessions/{$sessionId}", [
                'headers' => $this->headers($server),
            ]);
            return $res->getStatusCode() === 200;
        } catch (GuzzleException) {
            return false;
        }
    }

    public function restoreAllSessions(WaServer $server): array
    {
        try {
            $res = $this->http->post($server->baseUrl() . '/sessions/restore-all', [
                'headers' => $this->headers($server),
                'json' => [],
            ]);
            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function send(WaServer $server, string $sessionId, string $phone, string $message): array
    {
        try {
            $res = $this->http->post($server->baseUrl() . "/sessions/{$sessionId}/send", [
                'headers' => $this->headers($server),
                'json' => [
                    'phone' => $phone,
                    'message' => $message,
                ],
            ]);
            return json_decode($res->getBody()->getContents(), true) ?? ['ok' => false, 'error' => 'Unknown error'];
        } catch (GuzzleException $e) {
            Log::error("BaileysService::send failed: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(WaServer $server, string $sessionId, array $recipients, string $message): array
    {
        try {
            $res = $this->http->post($server->baseUrl() . "/sessions/{$sessionId}/send-bulk", [
                'headers' => $this->headers($server),
                'json' => [
                    'recipients' => $recipients,
                    'message' => $message,
                ],
            ]);
            return json_decode($res->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            Log::error("BaileysService::sendBulk failed: {$e->getMessage()}");
            return ['sent' => 0, 'failed' => count($recipients), 'errors' => []];
        }
    }

    protected function headers(WaServer $server): array
    {
        return [
            'X-API-Key' => $server->api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
