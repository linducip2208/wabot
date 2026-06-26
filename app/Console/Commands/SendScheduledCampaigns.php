<?php

namespace App\Console\Commands;

use App\Models\WaCampaign;
use App\Models\WaContact;
use App\Services\BaileysService;
use Illuminate\Console\Command;

class SendScheduledCampaigns extends Command
{
    protected $signature = 'wabot:send-scheduled';
    protected $description = 'Send scheduled campaigns that are due';

    public function __construct(
        protected BaileysService $baileys,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $campaigns = WaCampaign::where('status', 'draft')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->limit(10)
            ->get();

        foreach ($campaigns as $campaign) {
            $session = $campaign->session;
            if (!$session || !$session->server || $session->status !== 'connected') {
                $campaign->update(['status' => 'failed']);
                continue;
            }

            $recipients = WaContact::whereIn('id', $campaign->recipient_ids ?? [])
                ->pluck('phone')->toArray();

            $campaign->update(['status' => 'sending']);

            $result = $this->baileys->sendBulk(
                $session->server, $session->session_id,
                $recipients, $campaign->message
            );

            $campaign->update([
                'status' => 'sent',
                'sent_count' => $result['sent'] ?? 0,
                'failed_count' => $result['failed'] ?? 0,
            ]);

            $this->info("Sent campaign #{$campaign->id}: {$result['sent']}/{$campaign->total_recipients}");
        }

        $this->info("Processed {$campaigns->count()} scheduled campaigns.");
    }
}
