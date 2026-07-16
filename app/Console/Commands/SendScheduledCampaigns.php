<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignJob;
use App\Models\WaCampaign;
use Illuminate\Console\Command;

class SendScheduledCampaigns extends Command
{
    protected $signature = 'wabot:send-scheduled';
    protected $description = 'Send scheduled campaigns that are due';

    public function handle()
    {
        $campaigns = WaCampaign::where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->limit(10)
            ->get();

        foreach ($campaigns as $campaign) {
            if (($campaign->channel ?? 'whatsapp') === 'whatsapp') {
                $session = $campaign->session;
                if (!$session || !$session->server || $session->status !== 'connected') {
                    $campaign->update(['status' => 'failed']);
                    continue;
                }
            }

            $campaign->update(['status' => 'sending']);
            SendCampaignJob::dispatch($campaign->id);

            $this->info("Dispatched campaign #{$campaign->id} ({$campaign->total_recipients} recipients)");
        }

        $this->info("Processed {$campaigns->count()} scheduled campaigns.");
    }
}
