<?php

namespace App\Http\Controllers;

use App\Models\WaAppointment;
use App\Models\WaAvailability;
use App\Models\WaContact;
use App\Models\WaService;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    protected AppointmentService $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $yearMonth = $request->get('month', now()->format('Y-m'));

        $calendar = $this->appointmentService->getCalendarData($userId, $yearMonth);

        $upcoming = WaAppointment::where('user_id', $userId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_at', '>=', now())
            ->with(['contact', 'service'])
            ->orderBy('start_at')
            ->limit(20)
            ->get();

        $services = WaService::where('user_id', $userId)->get();
        $availabilities = WaAvailability::where('user_id', $userId)->orderBy('day_of_week')->orderBy('start_time')->get();
        $contacts = WaContact::where('user_id', $userId)->orderBy('name')->limit(200)->get();
        $pastAppointments = WaAppointment::where('user_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->with(['contact', 'service'])
            ->orderByDesc('start_at')
            ->limit(10)
            ->get();

        return view('appointments.index', compact(
            'calendar', 'upcoming', 'services', 'availabilities',
            'contacts', 'pastAppointments', 'yearMonth'
        ));
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'nullable|exists:wa_services,id',
            'date' => 'required|date',
        ]);

        $slots = $this->appointmentService->getAvailableSlots(
            Auth::id(),
            $request->service_id,
            $request->date
        );

        return response()->json(['slots' => $slots]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:wa_contacts,id',
            'service_id' => 'required|exists:wa_services,id',
            'datetime' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->appointmentService->book(
            Auth::id(),
            $validated['contact_id'],
            $validated['service_id'],
            $validated['datetime'],
            $validated['notes'] ?? null,
        );

        if ($result['ok'] ?? false) {
            return redirect()->route('appointments.index')->with('success', __('messages.success.appointment_created'));
        }

        return back()->with('error', $result['error'] ?? 'Booking failed.')->withInput();
    }

    public function update(Request $request, WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'start_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $result = $this->appointmentService->reschedule($appointment, $validated['start_at']);
        if ($request->has('notes')) {
            $appointment->update(['notes' => $validated['notes']]);
        }

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.appointment_rescheduled'));
        }

        return back()->with('error', $result['error'] ?? 'Reschedule failed.');
    }

    public function confirm(WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);

        if (!in_array($appointment->status, ['pending'])) {
            return back()->with('error', 'Only pending appointments can be confirmed.');
        }

        $appointment->update(['status' => 'confirmed']);
        return back()->with('success', __('messages.success.appointment_confirmed'));
    }

    public function cancel(WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);

        if (in_array($appointment->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel this appointment.');
        }

        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', __('messages.success.appointment_cancelled'));
    }

    public function complete(WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);

        if ($appointment->status !== 'confirmed') {
            return back()->with('error', 'Only confirmed appointments can be completed.');
        }

        $appointment->update(['status' => 'completed']);
        return back()->with('success', __('messages.success.appointment_completed'));
    }

    public function sendReminder(WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);

        $result = $this->appointmentService->sendReminder($appointment);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.reminder_sent'));
        }

        return back()->with('error', $result['error'] ?? 'Failed to send reminder.');
    }

    public function destroy(WaAppointment $appointment)
    {
        abort_if($appointment->user_id !== Auth::id(), 403);
        $appointment->delete();
        return back()->with('success', __('messages.success.appointment_deleted'));
    }

    // ── Service Management ──────────────────────────────────────

    public function serviceStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:20',
        ]);

        WaService::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'price' => $validated['price'] ?? 0,
            'color' => $validated['color'] ?? '#3b82f6',
        ]);

        return back()->with('success', __('messages.success.service_created'));
    }

    public function serviceUpdate(Request $request, WaService $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);
        return back()->with('success', __('messages.success.service_updated'));
    }

    public function serviceDestroy(WaService $service)
    {
        abort_if($service->user_id !== Auth::id(), 403);

        $hasAppointments = WaAppointment::where('service_id', $service->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasAppointments) {
            $service->update(['is_active' => false]);
            return back()->with('success', __('messages.success.service_deactivated'));
        }

        $service->delete();
        return back()->with('success', __('messages.success.service_deleted'));
    }

    // ── Availability Management ─────────────────────────────────

    public function availabilityStore(Request $request)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        WaAvailability::create([
            'user_id' => Auth::id(),
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
        ]);

        return back()->with('success', __('messages.success.availability_added'));
    }

    public function availabilityToggle(WaAvailability $availability)
    {
        abort_if($availability->user_id !== Auth::id(), 403);
        $availability->update(['is_active' => !$availability->is_active]);
        return back()->with('success', __('messages.success.availability_toggled'));
    }

    public function availabilityDestroy(WaAvailability $availability)
    {
        abort_if($availability->user_id !== Auth::id(), 403);
        $availability->delete();
        return back()->with('success', __('messages.success.availability_deleted'));
    }
}
