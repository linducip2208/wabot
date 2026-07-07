<?php

namespace App\Services;

use App\Models\WaSlaLog;
use App\Models\WaSlaConfig;
use App\Models\WaMessage;

class SlaService
{
    /**
     * Start SLA tracking saat customer kirim pesan.
     */
    public function start(int $userId, int $contactId): ?WaSlaLog
    {
        $config = WaSlaConfig::where('user_id', $userId)->where('is_active', true)->first();
        if (!$config) return null;

        return WaSlaLog::create([
            'user_id' => $userId,
            'contact_id' => $contactId,
            'sla_config_id' => $config->id,
            'customer_message_at' => now(),
        ]);
    }

    /**
     * Catat first response dari agent.
     */
    public function recordResponse(int $userId, int $contactId): void
    {
        $log = WaSlaLog::where('user_id', $userId)
            ->where('contact_id', $contactId)
            ->whereNull('first_response_at')
            ->latest()
            ->first();

        if (!$log || !$log->slaConfig) return;

        $now = now();
        $log->update(['first_response_at' => $now]);

        $minutesElapsed = $log->customer_message_at->diffInMinutes($now);
        if ($minutesElapsed > $log->slaConfig->first_response_minutes) {
            $log->update(['first_response_breached' => true]);
        }
    }

    /**
     * Resolve conversation SLA.
     */
    public function resolve(int $userId, int $contactId): void
    {
        $log = WaSlaLog::where('user_id', $userId)
            ->where('contact_id', $contactId)
            ->whereNull('resolved_at')
            ->latest()
            ->first();

        if (!$log || !$log->slaConfig) return;

        $now = now();
        $log->update(['resolved_at' => $now]);

        $minutesElapsed = $log->customer_message_at->diffInMinutes($now);
        if ($minutesElapsed > $log->slaConfig->resolution_minutes) {
            $log->update(['resolution_breached' => true]);
        }
    }

    public function getStats(int $userId): array
    {
        $today = WaSlaLog::where('user_id', $userId)->whereDate('created_at', today());
        return [
            'total' => $today->count(),
            'first_response_breach' => $today->clone()->where('first_response_breached', true)->count(),
            'resolution_breach' => $today->clone()->where('resolution_breached', true)->count(),
            'avg_first_response_minutes' => round($today->clone()->whereNotNull('first_response_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, customer_message_at, first_response_at)) as avg')
                ->value('avg') ?? 0, 1),
        ];
    }
}
