<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaClickEvent extends Model
{
    protected $fillable = [
        'user_id', 'campaign_id', 'contact_id', 'token', 'link_url',
        'user_agent', 'ip_address', 'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WaCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id')->withDefault();
    }
}
