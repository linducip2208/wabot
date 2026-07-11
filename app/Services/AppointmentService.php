<?php

namespace App\Services;

use App\Models\WaAppointment;
use App\Models\WaAvailability;
use App\Models\WaContact;
use App\Models\WaService;
use App\Models\WaSession;
use App\Services\BaileysService;
use App\Services\MetaApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentService
{
    protected BaileysService $baileys;
    protected MetaApiService $metaApi;

    public function __construct(BaileysService $baileys, MetaApiService $metaApi)
    {
        $this->baileys = $baileys;
        $this->metaApi = $metaApi;
    }

    /**
     * Get available time slots for a service on a given date.
     */
    public function getAvailableSlots(int $userId, ?int $serviceId, string $dateString): array
    {
        $date = Carbon::parse($dateString);
        $dayOfWeek = $date->dayOfWeek;

        $availabilities = WaAvailability::where('user_id', $userId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($availabilities->isEmpty()) {
            return [];
        }

        $duration = 30;
        if ($serviceId) {
            $service = WaService::where('user_id', $userId)->find($serviceId);
            if ($service) {
                $duration = $service->duration_minutes;
            }
        }

        $bookedSlots = WaAppointment::where('user_id', $userId)
            ->whereDate('start_at', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_at')
            ->get()
            ->map(fn($a) => [
                'start' => Carbon::parse($a->start_at),
                'end' => Carbon::parse($a->end_at),
            ]);

        $slots = [];

        foreach ($availabilities as $avail) {
            $startTime = Carbon::parse($date->toDateString() . ' ' . $avail->start_time);
            $endTime = Carbon::parse($date->toDateString() . ' ' . $avail->end_time);

            $current = $startTime->copy();
            while ($current->copy()->addMinutes($duration)->lte($endTime)) {
                $slotEnd = $current->copy()->addMinutes($duration);
                $conflict = $bookedSlots->first(fn($b) =>
                    $current->lt($b['end']) && $slotEnd->gt($b['start'])
                );

                if (!$conflict) {
                    $slots[] = [
                        'start' => $current->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime' => $current->toDateTimeString(),
                    ];
                }

                $current->addMinutes($duration);
            }
        }

        return $slots;
    }

    /**
     * Check for double-booking conflict.
     */
    public function checkConflict(int $userId, string $datetime, int $durationMinutes, ?int $excludeAppointmentId = null): bool
    {
        $startAt = Carbon::parse($datetime);
        $endAt = $startAt->copy()->addMinutes($durationMinutes);

        $query = WaAppointment::where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        return $query->exists();
    }

    /**
     * Book a new appointment.
     */
    public function book(int $userId, int $contactId, int $serviceId, string $datetime, ?string $notes = null): array
    {
        $service = WaService::where('user_id', $userId)->findOrFail($serviceId);
        $startAt = Carbon::parse($datetime);
        $endAt = $startAt->copy()->addMinutes($service->duration_minutes);

        if ($this->checkConflict($userId, $datetime, $service->duration_minutes)) {
            return ['ok' => false, 'error' => 'Time slot is already booked.'];
        }

        $appointment = WaAppointment::create([
            'user_id' => $userId,
            'contact_id' => $contactId,
            'service_id' => $serviceId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'pending',
            'notes' => $notes,
        ]);

        return ['ok' => true, 'appointment' => $appointment->load(['contact', 'service'])];
    }

    /**
     * Reschedule an appointment.
     */
    public function reschedule(WaAppointment $appointment, string $newDatetime): array
    {
        $service = $appointment->service;
        $startAt = Carbon::parse($newDatetime);
        $endAt = $startAt->copy()->addMinutes($service->duration_minutes);

        if ($this->checkConflict($appointment->user_id, $newDatetime, $service->duration_minutes, $appointment->id)) {
            return ['ok' => false, 'error' => 'Time slot is already booked.'];
        }

        $appointment->update([
            'start_at' => $startAt,
            'end_at' => $endAt,
            'status' => 'pending',
        ]);

        return ['ok' => true, 'appointment' => $appointment->fresh(['contact', 'service'])];
    }

    /**
     * Send a WhatsApp reminder for the given appointment.
     */
    public function sendReminder(WaAppointment $appointment): array
    {
        $contact = $appointment->contact;
        $service = $appointment->service;
        $phone = preg_replace('/^tg:|^ig:|^fb:|^dc:|^gbm:|^sms:|^email:|^tt:|^line:|^x:/', '', $contact->phone);
        $startFormatted = $appointment->start_at->format('d M Y, H:i');

        $message = "📅 *Appointment Reminder*\n\n"
            . "Service: *{$service->name}*\n"
            . "Date: {$startFormatted}\n"
            . "Duration: {$service->duration_minutes} mins\n\n"
            . "Reply *OK* to confirm or *CANCEL* to cancel.";

        $session = WaSession::where('user_id', $appointment->user_id)
            ->where('status', 'connected')
            ->first();

        if (!$session) {
            return ['ok' => false, 'error' => 'No connected session.'];
        }

        if ($session->meta_account_id) {
            $metaAccount = \App\Models\WaMetaAccount::find($session->meta_account_id);
            if ($metaAccount && $metaAccount->is_active) {
                $result = $this->metaApi->sendText($metaAccount, $phone, $message);
                return ['ok' => empty($result['error']), 'error' => $result['error'] ?? null];
            }
        }

        $result = $this->baileys->send($session->server, $session->session_id, $phone, $message);
        return ['ok' => $result['ok'] ?? false, 'error' => $result['error'] ?? null];
    }

    /**
     * Get formatted date info for calendar rendering.
     */
    public function getCalendarData(int $userId, string $yearMonth): array
    {
        $date = Carbon::parse($yearMonth . '-01');
        $daysInMonth = $date->daysInMonth;
        $firstDayOfWeek = $date->copy()->startOfMonth()->dayOfWeek;

        $appointments = WaAppointment::where('user_id', $userId)
            ->whereYear('start_at', $date->year)
            ->whereMonth('start_at', $date->month)
            ->with(['contact', 'service'])
            ->orderBy('start_at')
            ->get()
            ->groupBy(fn($a) => $a->start_at->format('Y-m-d'));

        $services = WaService::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $availabilities = WaAvailability::where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        return [
            'year' => $date->year,
            'month' => $date->month,
            'monthName' => $date->format('F'),
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'appointments' => $appointments,
            'services' => $services,
            'availabilities' => $availabilities,
            'prevMonth' => $date->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $date->copy()->addMonth()->format('Y-m'),
        ];
    }
}
