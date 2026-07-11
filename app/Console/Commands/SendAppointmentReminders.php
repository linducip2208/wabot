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
        $windowEnd = $now->copy()->addMinutes(30);

        $appointments = WaAppointment::where('status', 'confirmed')
            ->where('start_at', '>=', $now)
            ->where('start_at', '<=', $windowEnd)
            ->whereNull('reminded_at')
            ->with(['contact', 'service'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            $result = $appointmentService->sendReminder($appointment);
            if ($result['ok'] ?? false) {
                $appointment->update(['reminded_at' => now()]);
                $count++;
            }
            $this->info("Reminder sent for appointment #{$appointment->id} - {$appointment->contact->name}");
        }

        $this->info("Sent {$count} appointment reminders.");
        return Command::SUCCESS;
    }
}
