<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'billing_period',
        'features', 'max_sessions', 'max_contacts',
        'max_autoreplies', 'max_campaign_recipients',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'features' => 'json',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getLimit(string $key): int
    {
        return (int) ($this->getAttributes()["max_{$key}"] ?? 0);
    }
}
