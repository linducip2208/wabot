<?php

namespace App\Http\Controllers;

use App\Models\WaSlaConfig;
use App\Models\WaSlaLog;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlaConfigController extends Controller
{
    public function index()
    {
        $configs = WaSlaConfig::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('sla.index', compact('configs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_response_minutes' => 'required|integer|min:1|max:10080',
            'resolution_minutes' => 'required|integer|min:1|max:10080',
            'business_hours_only' => 'nullable|boolean',
        ]);

        WaSlaConfig::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'first_response_minutes' => $validated['first_response_minutes'],
            'resolution_minutes' => $validated['resolution_minutes'],
            'business_hours_only' => $request->boolean('business_hours_only'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Konfigurasi SLA berhasil disimpan.');
    }

    public function update(Request $request, WaSlaConfig $config)
    {
        abort_if($config->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_response_minutes' => 'required|integer|min:1|max:10080',
            'resolution_minutes' => 'required|integer|min:1|max:10080',
            'business_hours_only' => 'nullable|boolean',
        ]);

        $config->update([
            'name' => $validated['name'],
            'first_response_minutes' => $validated['first_response_minutes'],
            'resolution_minutes' => $validated['resolution_minutes'],
            'business_hours_only' => $request->boolean('business_hours_only'),
        ]);

        return back()->with('success', 'Konfigurasi SLA berhasil diperbarui.');
    }

    public function destroy(WaSlaConfig $config)
    {
        abort_if($config->user_id !== Auth::id(), 403);
        $config->delete();

        return back()->with('success', 'Konfigurasi SLA dihapus.');
    }

    public function logs(Request $request)
    {
        $query = WaSlaLog::where('user_id', Auth::id())
            ->with(['contact', 'slaConfig', 'teamMember']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->has('breach_filter')) {
            $b = $request->breach_filter;
            if ($b === 'first') {
                $query->where('first_response_breached', true);
            } elseif ($b === 'resolution') {
                $query->where('resolution_breached', true);
            } elseif ($b === 'any') {
                $query->where(function ($q) {
                    $q->where('first_response_breached', true)
                      ->orWhere('resolution_breached', true);
                });
            }
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $configs = WaSlaConfig::where('user_id', Auth::id())->get();

        return view('sla.logs', compact('logs', 'configs'));
    }

    public function dashboard()
    {
        $service = app(SlaService::class);
        $stats = $service->getStats(Auth::id());

        return response()->json($stats);
    }
}
