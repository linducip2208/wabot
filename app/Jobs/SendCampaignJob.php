<?php

namespace App\Jobs;

use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Services\CampaignSenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;
    public int $tries = 1;
    public int $timeout = 0;

    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function handle(CampaignSenderService $sender): void
    {
        $campaign = WaCampaign::find($this->campaignId);

        if (!$campaign) {
            Log::warning("SendCampaignJob: Campaign #{$this->campaignId} not found.");
            return;
        }

        if ($campaign->status !== 'sending') {
            return;
        }

        $recipients = WaContact::whereIn('id', $campaign->recipient_ids ?? [])
            ->orderBy('id')
            ->get();

        $sender->send($campaign, $recipients);
    }
}
