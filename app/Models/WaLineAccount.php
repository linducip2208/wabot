<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaLineAccount extends Model
{
    protected $table = 'wa_line_accounts';

    protected $fillable = [
        'user_id', 'name', 'channel_id', 'channel_secret',
        'channel_access_token', 'bot_basic_id', 'display_name',
        'picture_url', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    protected $hidden = ['channel_secret', 'channel_access_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
