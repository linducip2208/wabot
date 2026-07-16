<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaDripEnrollment extends Model
{
    protected $fillable = [
        'drip_campaign_id', 'contact_id', 'current_step', 'next_send_at', 'status',
    ];

    protected $casts = [
        'next_send_at' => 'datetime',
    ];

    public function dripCampaign(): BelongsTo
    {
        return $this->belongsTo(WaDripCampaign::class, 'drip_campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }
}
