<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaAiKey extends Model
{
    protected $fillable = [
        'user_id', 'name', 'provider', 'model', 'base_url', 'api_key_encrypted',
        'system_prompt', 'max_tokens', 'temperature', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'temperature' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function autoreplies(): HasMany
    {
        return $this->hasMany(WaAutoreply::class, 'ai_key_id');
    }
}
