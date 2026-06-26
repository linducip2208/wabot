<?php

namespace App\Console\Commands;

use App\Models\WaRecurring;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use Illuminate\Console\Command;

class ProcessRecurringSchedules extends Command
{
    protected $signature = 'wabot:recurring';
    protected $description = 'Process recurring WhatsApp schedules';

    public function handle(BaileysService $baileys, SpintaxService $spintax): void
    {
        $now = now();
        $schedules = WaRecurring::where('is_active', true)
            ->where('next_run_at', '<=', $now)
            ->get();

        foreach ($schedules as $s) {
            $session = $s->session_id
                ? WaSession::find($s->session_id)
                : WaSession::where('user_id', $s->user_id)->where('status', 'connected')->first();

            if (!$session || !$session->server) continue;

            $contacts = match ($s->target_type) {
                'all' => WaContact::where('user_id', $s->user_id)->get(),
                'numbers' => WaContact::where('user_id', $s->user_id)->whereIn('id', $s->target_ids ?? [])->get(),
                default => collect(),
            };

            foreach ($contacts as $contact) {
                $phone = $contact->phone;
                $message = $spintax->process($s->message, [
                    'name' => $contact->name,
                    'phone' => preg_replace('/@.*$/', '', $phone),
                ]);

                $baileys->send($session->server, $session->session_id, $phone, $message);
                usleep(500000 + random_int(0, 1000000));
            }

            $s->update(['last_sent_at' => $now]);
            if ($s->recurrence !== 'once') {
                $s->computeNextRun();
            } else {
                $s->update(['is_active' => false, 'next_run_at' => null]);
            }

            $this->info("Processed: {$s->name} ({$contacts->count()} contacts)");
        }

        $this->info('Done.');
    }
}
