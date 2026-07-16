<?php

namespace App\Console\Commands;

use App\Models\WaContact;
use App\Models\WaDripCampaign;
use App\Models\WaDripEnrollment;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use Illuminate\Console\Command;

class ProcessDripCampaigns extends Command
{
    protected $signature = 'wabot:process-drips';
    protected $description = 'Enroll contacts and send due drip campaign steps';

    public function handle(BaileysService $baileys, SpintaxService $spintax): void
    {
        $drips = WaDripCampaign::where('is_active', true)
            ->with(['steps', 'session.server'])
            ->get();

        foreach ($drips as $drip) {
            $session = $drip->session;
            if (!$session || !$session->server || $session->status !== 'connected') {
                continue;
            }

            if ($drip->steps->isEmpty()) {
                continue;
            }

            $this->enrollContacts($drip);
            $this->sendDueSteps($drip, $baileys, $spintax);
        }

        $this->info("Processed {$drips->count()} active drip campaigns.");
    }

    protected function enrollContacts(WaDripCampaign $drip): void
    {
        $query = WaContact::where('user_id', $drip->user_id)
            ->whereNotIn('id', $drip->enrollments()->pluck('contact_id'));

        if ($drip->send_to_new_only) {
            $query->where('created_at', '>=', $drip->activated_at ?? $drip->created_at);
        }

        $firstStep = $drip->steps->first();

        foreach ($query->get() as $contact) {
            WaDripEnrollment::create([
                'drip_campaign_id' => $drip->id,
                'contact_id' => $contact->id,
                'current_step' => 0,
                'next_send_at' => now()->addHours($firstStep->wait_hours),
                'status' => 'active',
            ]);
        }
    }

    protected function sendDueSteps(WaDripCampaign $drip, BaileysService $baileys, SpintaxService $spintax): void
    {
        $due = $drip->enrollments()
            ->where('status', 'active')
            ->where('next_send_at', '<=', now())
            ->with('contact')
            ->limit(50)
            ->get();

        $steps = $drip->steps->values();

        foreach ($due as $enrollment) {
            $contact = $enrollment->contact;
            if (!$contact) {
                $enrollment->update(['status' => 'stopped']);
                continue;
            }

            $step = $steps->get($enrollment->current_step);
            if (!$step) {
                $enrollment->update(['status' => 'completed']);
                continue;
            }

            $phone = preg_replace('/@.*$/', '', $contact->phone);
            $message = $spintax->process($step->message, ['name' => $contact->name, 'phone' => $phone]);

            if ($step->media_url) {
                $baileys->sendMedia($drip->session->server, $drip->session->session_id, $phone, $step->media_url, $message);
            } else {
                $baileys->send($drip->session->server, $drip->session->session_id, $phone, $message);
            }

            $nextIndex = $enrollment->current_step + 1;
            $nextStep = $steps->get($nextIndex);

            if ($nextStep) {
                $enrollment->update([
                    'current_step' => $nextIndex,
                    'next_send_at' => now()->addHours($nextStep->wait_hours),
                ]);
            } else {
                $enrollment->update([
                    'current_step' => $nextIndex,
                    'next_send_at' => null,
                    'status' => 'completed',
                ]);
            }

            usleep(500000 + random_int(0, 1000000));
        }
    }
}
