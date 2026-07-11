<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaRssScheduleHistory extends Model
{
    protected $fillable = [
        'rss_schedule_id', 'post_url', 'content_hash', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function rssSchedule(): BelongsTo
    {
        return $this->belongsTo(WaRssSchedule::class);
    }
}
