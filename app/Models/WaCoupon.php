<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCoupon extends Model
{
    protected $fillable = [
        'code', 'plan_id', 'discount_type', 'discount_value',
        'min_order', 'max_uses', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses > 0 && $this->used_count >= $this->max_uses) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            return $amount * ($this->discount_value / 100);
        }
        return min($this->discount_value, $amount);
    }
}
