<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaGbmAccount extends Model
{
    protected $table = 'wa_gbm_accounts';

    protected $fillable = [
        'user_id', 'name', 'brand_id', 'agent_id',
        'service_account_json', 'is_active', 'connected_at',
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
