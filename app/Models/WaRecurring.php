<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaRecurring extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'channel', 'meta_account_id', 'telegram_account_id',
        'name', 'recurrence', 'time', 'day_of_week', 'day_of_month',
        'message', 'media_url', 'target_type', 'target_ids',
        'is_active', 'last_sent_at', 'next_run_at',
    ];

    protected $casts = [
        'target_ids' => 'json',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }

    public function telegramAccount(): BelongsTo
    {
        return $this->belongsTo(WaTelegramAccount::class, 'telegram_account_id');
    }

    public function computeNextRun(): void
    {
        $now = now();
        $time = $this->time ?? '08:00:00';
        [$h, $m, $s] = explode(':', $time);

        $next = match ($this->recurrence) {
            'once' => $now->copy()->setTime((int)$h, (int)$m, 0),
            'daily' => $now->copy()->setTime((int)$h, (int)$m, 0)->addDay(),
            'weekly' => $now->copy()->next((int)($this->day_of_week ?? 1))->setTime((int)$h, (int)$m, 0),
            'monthly' => $now->copy()->setTime((int)$h, (int)$m, 0)->setDay(min((int)($this->day_of_month ?? 1), $now->copy()->addMonth()->daysInMonth)),
            default => $now->copy()->addDay()->setTime((int)$h, (int)$m, 0),
        };

        if ($next->lte($now)) {
            $next = match ($this->recurrence) {
                'daily' => $next->addDay(),
                'weekly' => $next->addWeek(),
                'monthly' => $next->addMonth(),
                default => $next->addDay(),
            };
        }

        $this->update(['next_run_at' => $next]);
    }
}
