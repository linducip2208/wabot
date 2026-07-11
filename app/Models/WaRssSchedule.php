<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaRssSchedule extends Model
{
    protected $fillable = [
        'user_id', 'name', 'feed_url', 'platform_targets',
        'interval_minutes', 'last_checked_at', 'is_active',
    ];

    protected $casts = [
        'platform_targets' => 'array',
        'last_checked_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WaRssScheduleHistory::class, 'rss_schedule_id');
    }
}
