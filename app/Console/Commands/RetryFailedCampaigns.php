<?php

namespace App\Console\Commands;

use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Services\BaileysService;
use Illuminate\Console\Command;

class RetryFailedCampaigns extends Command
{
    protected $signature = 'wabot:retry-campaigns';
    protected $description = 'Retry sending failed campaign messages';

    public function __construct(
        protected BaileysService $baileys,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $campaigns = WaCampaign::where('status', 'failed')
            ->where('updated_at', '<', now()->subHour())
            ->limit(5)
            ->get();

        foreach ($campaigns as $campaign) {
            $session = $campaign->session;
            if (!$session || !$session->server || $session->status !== 'connected') {
                continue;
            }

            $recipients = WaContact::whereIn('id', $campaign->recipient_ids ?? [])
                ->pluck('phone')->toArray();

            $result = $this->baileys->sendBulk(
                $session->server, $session->session_id,
                $recipients, $campaign->message
            );

            $campaign->update([
                'status' => $result['failed'] > 0 ? 'failed' : 'sent',
                'sent_count' => ($campaign->sent_count + ($result['sent'] ?? 0)),
                'failed_count' => $result['failed'] ?? 0,
            ]);

            $this->info("Retried campaign #{$campaign->id}: {$result['sent']} sent, {$result['failed']} failed");
        }

        $this->info('Retry complete.');
    }
}
