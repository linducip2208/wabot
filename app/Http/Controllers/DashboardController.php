<?php

namespace App\Http\Controllers;

use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Models\WaCampaign;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $stats = [
            'sessions' => WaSession::where('user_id', $userId)->count(),
            'sessions_connected' => WaSession::where('user_id', $userId)->where('status', 'connected')->count(),
            'contacts' => WaContact::where('user_id', $userId)->count(),
            'campaigns' => WaCampaign::where('user_id', $userId)->count(),
            'messages_in' => WaMessage::where('user_id', $userId)->where('direction', 'in')->count(),
            'messages_out' => WaMessage::where('user_id', $userId)->where('direction', 'out')->count(),
        ];

        $recentMessages = WaMessage::where('user_id', $userId)
            ->with('contact', 'session')
            ->latest()
            ->take(10)
            ->get();

        $sessions = WaSession::where('user_id', $userId)
            ->with('server')
            ->get();

        // Chart data: last 7 days messages
        $chartLabels = [];
        $chartIn = [];
        $chartOut = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartLabels[] = now()->subDays($i)->translatedFormat('d M');
            $chartIn[] = WaMessage::where('user_id', $userId)->where('direction', 'in')->whereDate('created_at', $date)->count();
            $chartOut[] = WaMessage::where('user_id', $userId)->where('direction', 'out')->whereDate('created_at', $date)->count();
        }

        // Today's hourly activity
        $hourlyLabels = [];
        $hourlyData = [];
        for ($h = 0; $h < 24; $h++) {
            $hourlyLabels[] = sprintf('%02d:00', $h);
            $hourlyData[] = WaMessage::where('user_id', $userId)
                ->whereRaw('HOUR(created_at) = ?', [$h])
                ->whereDate('created_at', today())
                ->count();
        }

        $chartData = [
            'labels' => $chartLabels,
            'in' => $chartIn,
            'out' => $chartOut,
            'hourlyLabels' => $hourlyLabels,
            'hourlyData' => $hourlyData,
        ];

        return view('dashboard', compact('stats', 'recentMessages', 'sessions', 'chartData'));
    }
}
