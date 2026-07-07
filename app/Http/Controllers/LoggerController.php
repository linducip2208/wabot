<?php

namespace App\Http\Controllers;

use App\Models\WaSessionLog;
use App\Models\WaWebhookLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class LoggerController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $sessionLogs = WaSessionLog::where('user_id', $userId)
            ->with('session')
            ->latest()
            ->get()
            ->map(fn($log) => [
                'id' => 'session_' . $log->id,
                'type' => 'session',
                'event' => $log->event,
                'detail' => 'Sesi: ' . ($log->session?->name ?? 'N/A') . ($log->phone ? ' | ' . $log->phone : '') . ($log->reason ? ' | ' . $log->reason : ''),
                'logged_at' => $log->logged_at ?? $log->created_at,
                'created_at' => $log->created_at,
            ]);

        $webhookLogs = WaWebhookLog::whereHas('webhook', fn($q) => $q->where('user_id', $userId))
            ->with('webhook')
            ->latest()
            ->get()
            ->map(fn($log) => [
                'id' => 'webhook_' . $log->id,
                'type' => 'webhook',
                'event' => $log->event,
                'detail' => 'Webhook: ' . ($log->webhook?->name ?? 'N/A') . ' | HTTP ' . $log->response_code . ($log->error ? ' | Error: ' . $log->error : ''),
                'logged_at' => $log->created_at,
                'created_at' => $log->created_at,
            ]);

        $logs = $sessionLogs->concat($webhookLogs)
            ->sortByDesc('created_at')
            ->values();

        return view('logger.index', compact('logs'));
    }
}
