<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaWebhook extends Model
{
    protected $fillable = [
        'user_id', 'name', 'url', 'events', 'is_active', 'last_triggered_at',
    ];

    protected $casts = [
        'events' => 'json',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WaWebhookLog::class, 'webhook_id');
    }
}
