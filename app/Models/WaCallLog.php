<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCallLog extends Model
{
    protected $fillable = [
        'broadcast_id', 'contact_id', 'meta_account_id', 'phone',
        'status', 'duration_seconds', 'call_id', 'audio_url', 'notes',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(WaCallBroadcast::class, 'broadcast_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }
}
