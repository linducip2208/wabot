<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCampaignAB extends Model
{
    protected $table = 'wa_campaign_ab';

    protected $fillable = [
        'user_id', 'session_id', 'name', 'variant_a_message', 'variant_b_message',
        'media_url_a', 'media_url_b', 'a_sent', 'a_replied', 'b_sent', 'b_replied',
        'winner', 'is_active', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'a_sent' => 'integer',
        'a_replied' => 'integer',
        'b_sent' => 'integer',
        'b_replied' => 'integer',
        'is_active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
