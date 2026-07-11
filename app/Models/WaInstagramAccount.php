<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaInstagramAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'instagram_id', 'username', 'access_token',
        'app_id', 'app_secret', 'webhook_verify_token', 'status',
        'is_active', 'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
