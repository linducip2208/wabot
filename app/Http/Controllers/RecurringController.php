<?php

namespace App\Http\Controllers;

use App\Models\WaRecurring;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecurringController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
    ) {}

    public function index()
    {
        $schedules = WaRecurring::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->get();

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('status', 'connected')
            ->get();

        $contacts = WaContact::where('user_id', Auth::id())->get();

        return view('recurrings.index', compact('schedules', 'sessions', 'contacts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'nullable|exists:wa_sessions,id',
            'recurrence' => 'required|in:once,daily,weekly,monthly',
            'time' => 'nullable',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'message' => 'required|string|max:5000',
            'target_type' => 'required|in:all,group,numbers',
            'is_active' => 'boolean',
        ]);

        $data['user_id'] = Auth::id();
        $data['is_active'] = $request->boolean('is_active', true);

        $schedule = WaRecurring::create($data);
        $schedule->computeNextRun();

        return back()->with('success', 'Jadwal berulang dibuat.');
    }

    public function update(Request $request, WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'nullable|exists:wa_sessions,id',
            'recurrence' => 'required|in:once,daily,weekly,monthly',
            'time' => 'nullable',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'message' => 'required|string|max:5000',
            'target_type' => 'required|in:all,group,numbers',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $schedule->update($data);
        $schedule->computeNextRun();

        return back()->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);
        $schedule->delete();
        return back()->with('success', 'Jadwal dihapus.');
    }

    public function toggle(WaRecurring $schedule)
    {
        abort_if($schedule->user_id !== Auth::id(), 403);
        $schedule->update(['is_active' => !$schedule->is_active]);
        return back()->with('success', $schedule->is_active ? 'Diaktifkan.' : 'Dinonaktifkan.');
    }
}
