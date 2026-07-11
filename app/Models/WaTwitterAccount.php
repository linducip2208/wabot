<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaTwitterAccount extends Model
{
    protected $table = 'wa_twitter_accounts';

    protected $fillable = [
        'user_id', 'name', 'client_id', 'client_secret',
        'twitter_user_id', 'username', 'access_token', 'refresh_token',
        'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    protected $hidden = ['client_secret', 'access_token', 'refresh_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
