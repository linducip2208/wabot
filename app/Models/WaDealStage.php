<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaDealStage extends Model
{
    protected $fillable = [
        'user_id', 'name', 'color', 'sort_order', 'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(WaDeal::class, 'stage_id');
    }
}
