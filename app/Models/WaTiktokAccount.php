<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTiktokAccount extends Model
{
    protected $table = 'wa_tiktok_accounts';

    protected $fillable = [
        'user_id', 'name', 'app_id', 'client_key', 'client_secret',
        'open_id', 'access_token', 'refresh_token', 'token_expires_at',
        'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = ['client_secret', 'access_token', 'refresh_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
