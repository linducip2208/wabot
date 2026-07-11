<?php

namespace App\Services;

use App\Models\WaSendGridAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class SendGridService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function sendEmail(WaSendGridAccount $account, string $to, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null): array
    {
        $fromAddress = $fromEmail ?: $account->from_email;
        $senderName = $fromName ?: $account->from_name;

        $payload = [
            'personalizations' => [
                ['to' => [['email' => $to]], 'subject' => $subject],
            ],
            'from' => ['email' => $fromAddress, 'name' => $senderName],
            'content' => [['type' => 'text/html', 'value' => $body]],
        ];

        return $this->call($account, $payload);
    }

    public function sendTemplate(WaSendGridAccount $account, string $to, string $templateId, array $data = [], ?string $fromEmail = null, ?string $fromName = null): array
    {
        $fromAddress = $fromEmail ?: $account->from_email;
        $senderName = $fromName ?: $account->from_name;

        $payload = [
            'personalizations' => [
                [
                    'to' => [['email' => $to]],
                    'dynamic_template_data' => $data,
                ],
            ],
            'from' => ['email' => $fromAddress, 'name' => $senderName],
            'template_id' => $templateId,
        ];

        return $this->call($account, $payload);
    }

    public function sendBulk(WaSendGridAccount $account, array $recipients, string $subject, string $body, ?string $fromEmail = null, ?string $fromName = null): array
    {
        $fromAddress = $fromEmail ?: $account->from_email;
        $senderName = $fromName ?: $account->from_name;

        $toRecipients = [];
        foreach ($recipients as $recipient) {
            if (is_string($recipient)) {
                $toRecipients[] = ['email' => $recipient];
            } else {
                $toRecipients[] = ['email' => $recipient['email'] ?? '', 'name' => $recipient['name'] ?? ''];
            }
        }

        $payload = [
            'personalizations' => [
                ['to' => $toRecipients, 'subject' => $subject],
            ],
            'from' => ['email' => $fromAddress, 'name' => $senderName],
            'content' => [['type' => 'text/html', 'value' => $body]],
        ];

        return $this->call($account, $payload);
    }

    protected function call(WaSendGridAccount $account, array $payload): array
    {
        try {
            $res = $this->http->post('https://api.sendgrid.com/v3/mail/send', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $account->getApiKeyAttribute(),
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $status = $res->getStatusCode();
            $body = $res->getBody()->getContents();

            if ($status >= 400) {
                Log::error("SendGrid API error: HTTP {$status}", ['body' => $body]);
                return ['ok' => false, 'status' => $status, 'body' => $body];
            }

            return ['ok' => true, 'status' => $status];
        } catch (GuzzleException $e) {
            Log::error("SendGridService HTTP error: {$e->getMessage()}");
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyApiKey(WaSendGridAccount $account): bool
    {
        try {
            $res = $this->http->get('https://api.sendgrid.com/v3/scopes', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $account->getApiKeyAttribute(),
                ],
            ]);

            return $res->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error("SendGrid verify failed: {$e->getMessage()}");
            return false;
        }
    }
}
