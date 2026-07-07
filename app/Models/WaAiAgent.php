<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAiAgent extends Model
{
    protected $fillable = [
        'user_id', 'ai_key_id', 'name', 'role', 'personality_prompt',
        'trigger_keywords', 'is_active', 'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiKey(): BelongsTo
    {
        return $this->belongsTo(WaAiKey::class, 'ai_key_id');
    }
}
