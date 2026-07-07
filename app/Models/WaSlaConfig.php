<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaSlaConfig extends Model
{
    protected $fillable = [
        'user_id', 'name', 'first_response_minutes', 'resolution_minutes',
        'business_hours_only', 'is_active',
    ];

    protected $casts = [
        'first_response_minutes' => 'integer',
        'resolution_minutes' => 'integer',
        'business_hours_only' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WaSlaLog::class, 'sla_config_id');
    }
}
