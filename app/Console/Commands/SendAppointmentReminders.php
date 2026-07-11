<?php

namespace App\Console\Commands;

use App\Models\WaAppointment;
use App\Services\AppointmentService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'wabot:appointment-reminders';
    protected $description = 'Send WhatsApp reminders for upcoming appointments';

    public function handle(AppointmentService $appointmentService): int
    {
        $now = now();
        $windowStart = $now->copy()->addMinutes(25);
        $windowEnd = $now->copy()->addMinutes(35);

        $appointments = WaAppointment::whereIn('status', ['confirmed'])
            ->where('start_at', '>=', $windowStart)
            ->where('start_at', '<=', $windowEnd)
            ->with(['contact', 'service'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            $result = $appointmentService->sendReminder($appointment);
            if ($result['ok'] ?? false) {
                $count++;
            }
            $this->info("Reminder sent for appointment #{$appointment->id} - {$appointment->contact->name}");
        }

        $this->info("Sent {$count} appointment reminders.");
        return Command::SUCCESS;
    }
}
