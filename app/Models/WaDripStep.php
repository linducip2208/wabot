<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaDripStep extends Model
{
    protected $fillable = [
        'drip_campaign_id', 'step_order', 'wait_hours',
        'message', 'media_url', 'ai_key_id',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'wait_hours' => 'integer',
    ];

    public function dripCampaign(): BelongsTo
    {
        return $this->belongsTo(WaDripCampaign::class, 'drip_campaign_id');
    }

    public function aiKey(): BelongsTo
    {
        return $this->belongsTo(WaAiKey::class, 'ai_key_id');
    }
}
