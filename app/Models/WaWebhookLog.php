<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaWebhookLog extends Model
{
    protected $fillable = [
        'webhook_id', 'event', 'response_code', 'response_body', 'error',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(WaWebhook::class, 'webhook_id');
    }
}
