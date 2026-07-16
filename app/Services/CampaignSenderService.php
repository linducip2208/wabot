<?php

namespace App\Services;

use App\Models\WaCampaign;
use Illuminate\Support\Collection;

class CampaignSenderService
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
        protected MetaApiService $metaApi,
        protected TelegramService $telegram,
        protected InstagramService $instagram,
        protected FacebookService $facebook,
        protected GbmService $gbm,
        protected DiscordService $discord,
        protected TwilioService $twilio,
        protected SendGridService $sendgrid,
    ) {}

    public function send(WaCampaign $campaign, Collection $recipients): void
    {
        $channel = $campaign->channel ?? 'whatsapp';

        if (!$this->channelReady($campaign, $channel)) {
            $campaign->update(['status' => 'failed']);
            return;
        }

        $phones = $recipients->pluck('phone')->toArray();
        $variables = [];
        foreach ($recipients as $r) {
            $phone = preg_replace('/@.*$/', '', $r->phone);
            $variables[$r->phone] = ['name' => $r->name, 'phone' => $phone];
        }

        $sent = $campaign->sent_count ?? 0;
        $failed = $campaign->failed_count ?? 0;
        $start = min($sent + $failed, count($phones));

        for ($i = $start; $i < count($phones); $i++) {
            $phone = $phones[$i];

            $campaign->refresh();
            if ($campaign->status !== 'sending') {
                return;
            }

            $msg = $campaign->message;
            if ($this->spintax->hasSpintax($msg) || str_contains($msg, '{name}')) {
                $msg = $this->spintax->process($msg, $variables[$phone] ?? []);
            }

            $to = preg_replace('/@.*$/', '', $phone);

            $result = match ($channel) {
                'meta' => $this->sendMetaMessage($campaign->metaAccount, $to, $msg),
                'telegram' => $this->sendTelegramMessage($campaign->telegramAccount, $to, $msg),
                'instagram' => $this->sendInstagramMessage($campaign->instagramAccount, $to, $msg),
                'facebook' => $this->sendFacebookMessage($campaign->facebookAccount, $to, $msg),
                'gbm' => $this->sendGbmMessage($campaign->gbmAccount, $to, $msg),
                'discord' => $this->sendDiscordMessage($campaign->discordAccount, $to, $msg),
                'tiktok' => $this->sendTiktokMessage($campaign->tiktokAccount, $to, $msg),
                'line' => $this->sendLineMessage($campaign->lineAccount, $to, $msg),
                'twitter' => $this->sendTwitterMessage($campaign->twitterAccount, $to, $msg),
                'sms' => $this->sendSmsMessage($campaign->twilioAccount, $to, $msg),
                'email' => $this->sendEmailMessage($campaign->sendgridAccount, $to, $msg),
                default => $this->sendBaileysMessage($campaign->session, $to, $msg),
            };

            if ($result) {
                $sent++;
            } else {
                $failed++;
            }

            $campaign->update([
                'sent_count' => $sent,
                'failed_count' => $failed,
            ]);

            if ($i < count($phones) - 1) {
                sleep($this->interval($campaign));
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
    }

    protected function interval(WaCampaign $campaign): int
    {
        $min = max(1, (int) ($campaign->delay_min_seconds ?? $campaign->delay_seconds ?? 300));
        $max = max($min, (int) ($campaign->delay_max_seconds ?? 400));

        return random_int($min, $max);
    }

    protected function channelReady(WaCampaign $campaign, string $channel): bool
    {
        return match ($channel) {
            'whatsapp' => $campaign->session && $campaign->session->server,
            'meta' => $campaign->metaAccount?->is_active ?? false,
            'telegram' => $campaign->telegramAccount?->is_active ?? false,
            'instagram' => $campaign->instagramAccount?->is_active ?? false,
            'facebook' => $campaign->facebookAccount?->is_active ?? false,
            'gbm' => $campaign->gbmAccount?->is_active ?? false,
            'discord' => $campaign->discordAccount?->is_active ?? false,
            'tiktok' => $campaign->tiktokAccount?->is_active ?? false,
            'line' => $campaign->lineAccount?->is_active ?? false,
            'twitter' => $campaign->twitterAccount?->is_active ?? false,
            'sms' => $campaign->twilioAccount?->is_active ?? false,
            'email' => $campaign->sendgridAccount?->is_active ?? false,
            default => true,
        };
    }

    protected function sendBaileysMessage($session, string $to, string $message): bool
    {
        $result = $this->baileys->send($session->server, $session->session_id, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendMetaMessage($metaAccount, string $to, string $message): bool
    {
        $result = $this->metaApi->sendText($metaAccount, $to, $message);
        return !isset($result['error']);
    }

    protected function sendTelegramMessage($tgAccount, string $to, string $message): bool
    {
        $result = $this->telegram->sendMessage($tgAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendInstagramMessage($igAccount, string $to, string $message): bool
    {
        $result = $this->instagram->sendDM($igAccount->instagram_id, $igAccount->access_token, $message, $to);
        return empty($result['error']);
    }

    protected function sendFacebookMessage($fbAccount, string $to, string $message): bool
    {
        $result = $this->facebook->sendMessage($fbAccount, $to, $message);
        return empty($result['error']);
    }

    protected function sendGbmMessage($gbmAccount, string $to, string $message): bool
    {
        $result = $this->gbm->sendMessage($gbmAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendDiscordMessage($dcAccount, string $to, string $message): bool
    {
        $result = $this->discord->sendMessage($dcAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendTiktokMessage($ttAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\TikTokService::class)->sendMessage($ttAccount->access_token, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendLineMessage($lineAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\LineService::class)->pushMessage($lineAccount, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendTwitterMessage($twAccount, string $to, string $message): bool
    {
        $result = app(\App\Services\TwitterService::class)->sendDM($twAccount->access_token, $to, $message);
        return $result['ok'] ?? false;
    }

    protected function sendSmsMessage($smsAccount, string $to, string $message): bool
    {
        $result = $this->twilio->sendSms($smsAccount, $to, $message);
        return !($result['error'] ?? false) && empty($result['error_code']);
    }

    protected function sendEmailMessage($sgAccount, string $to, string $message): bool
    {
        $result = $this->sendgrid->sendEmail($sgAccount, $to, 'WABot Campaign', $message);
        return $result['ok'] ?? false;
    }
}
