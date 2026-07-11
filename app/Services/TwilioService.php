<?php

namespace App\Services;

use App\Models\WaTwilioAccount;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 30, 'http_errors' => false]);
    }

    public function sendSms(WaTwilioAccount $account, string $to, string $message, ?string $from = null): array
    {
        $fromNumber = $from ?: $account->phone_number;
        return $this->send($account, $to, $fromNumber, $message);
    }

    public function sendWhatsApp(WaTwilioAccount $account, string $to, string $message, ?string $from = null): array
    {
        $fromNumber = $from ?: $account->phone_number;
        $waFrom = "whatsapp:{$fromNumber}";
        $waTo = "whatsapp:{$to}";
        return $this->send($account, $waTo, $waFrom, $message);
    }

    protected function send(WaTwilioAccount $account, string $to, string $from, string $message): array
    {
        try {
            $res = $this->http->post($account->baseUrl(), [
                'auth' => [$account->getAccountSidAttribute(), $account->getAuthTokenAttribute()],
                'form_params' => [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ],
            ]);

            $body = json_decode($res->getBody()->getContents(), true) ?? [];

            if (!empty($body['error_code']) || !empty($body['code'])) {
                Log::error("Twilio API error: {$to}", ['body' => $body]);
            }

            return $body;
        } catch (GuzzleException $e) {
            Log::error("TwilioService HTTP error: {$e->getMessage()}");
            return ['error' => true, 'error_message' => $e->getMessage()];
        }
    }

    public function verifyCredentials(WaTwilioAccount $account): bool
    {
        $sid = $account->getAccountSidAttribute();
        $token = $account->getAuthTokenAttribute();

        try {
            $res = $this->http->get("https://api.twilio.com/2010-04-01/Accounts/{$sid}.json", [
                'auth' => [$sid, $token],
            ]);

            return $res->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error("Twilio verify failed: {$e->getMessage()}");
            return false;
        }
    }
}
