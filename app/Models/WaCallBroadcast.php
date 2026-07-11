<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaCallBroadcast extends Model
{
    protected $fillable = [
        'user_id', 'meta_account_id', 'name', 'message', 'voice_id',
        'recipient_ids', 'status', 'total_recipients', 'called_count',
        'answered_count', 'failed_count', 'delay_seconds', 'scheduled_at',
    ];

    protected $casts = [
        'recipient_ids' => 'json',
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WaCallLog::class, 'broadcast_id');
    }
}
