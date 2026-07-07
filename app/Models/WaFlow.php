<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaFlow extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'trigger_keyword',
        'trigger_match_type', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(WaFlowNode::class, 'flow_id')->orderBy('sort_order');
    }

    public function autoreplies(): HasMany
    {
        return $this->hasMany(WaAutoreply::class, 'flow_id');
    }
}
