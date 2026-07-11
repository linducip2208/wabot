<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaDiscordAccount extends Model
{
    protected $table = 'wa_discord_accounts';

    protected $fillable = [
        'user_id', 'name', 'bot_token', 'bot_name', 'guild_id',
        'application_id', 'public_key', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
