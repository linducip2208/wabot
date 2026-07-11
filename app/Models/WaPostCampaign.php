<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaPostCampaign extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(WaPost::class, 'campaign_id');
    }
}
