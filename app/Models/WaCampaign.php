<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCampaign extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'name', 'message', 'delay_seconds',
        'media_url', 'message_type', 'recipient_ids', 'status',
        'total_recipients', 'sent_count', 'failed_count', 'scheduled_at',
    ];

    protected $casts = [
        'recipient_ids' => 'json',
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }
}
