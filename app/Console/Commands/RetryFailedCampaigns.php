<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignJob;
use App\Models\WaCampaign;
use Illuminate\Console\Command;

class RetryFailedCampaigns extends Command
{
    protected $signature = 'wabot:retry-campaigns';
    protected $description = 'Retry sending failed campaign messages';

    public function handle()
    {
        $campaigns = WaCampaign::where('status', 'failed')
            ->where('updated_at', '<', now()->subHour())
            ->limit(5)
            ->get();

        foreach ($campaigns as $campaign) {
            if (($campaign->channel ?? 'whatsapp') === 'whatsapp') {
                $session = $campaign->session;
                if (!$session || !$session->server || $session->status !== 'connected') {
                    continue;
                }
            }

            $campaign->update(['status' => 'sending']);
            SendCampaignJob::dispatch($campaign->id);

            $this->info("Retry dispatched for campaign #{$campaign->id}");
        }

        $this->info('Retry complete.');
    }
}
