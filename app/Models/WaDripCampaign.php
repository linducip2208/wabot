<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaDripCampaign extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'name', 'is_active', 'send_to_new_only', 'activated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'send_to_new_only' => 'boolean',
        'activated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WaDripStep::class, 'drip_campaign_id')->orderBy('step_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(WaDripEnrollment::class, 'drip_campaign_id');
    }
}
