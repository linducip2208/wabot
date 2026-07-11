<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTelegramAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'bot_token', 'bot_username', 'bot_id',
        'status', 'is_active', 'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function baseUrl(): string
    {
        return "https://api.telegram.org/bot{$this->bot_token}";
    }
}
