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
        'can_manage_server', 'is_active', 'sort_order',
        'can_use_meta', 'can_use_forms', 'can_use_calling',
        'can_use_instagram', 'can_use_flow', 'can_use_ai_agent',
        'can_use_intent', 'can_use_drip', 'can_use_ab_test',
        'can_use_catalog', 'can_use_commerce', 'can_use_deals',
        'can_use_kanban', 'max_meta_accounts', 'max_forms',
    ];

    protected $casts = [
        'features' => 'json',
        'is_active' => 'boolean',
        'can_manage_server' => 'boolean',
        'can_use_meta' => 'boolean',
        'can_use_forms' => 'boolean',
        'can_use_calling' => 'boolean',
        'can_use_instagram' => 'boolean',
        'can_use_flow' => 'boolean',
        'can_use_ai_agent' => 'boolean',
        'can_use_intent' => 'boolean',
        'can_use_drip' => 'boolean',
        'can_use_ab_test' => 'boolean',
        'can_use_catalog' => 'boolean',
        'can_use_commerce' => 'boolean',
        'can_use_deals' => 'boolean',
        'can_use_kanban' => 'boolean',
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

    public function vouchers(): HasMany
    {
        return $this->hasMany(WaVoucher::class);
    }

    public function getLimit(string $key): int
    {
        return (int) ($this->getAttributes()["max_{$key}"] ?? 0);
    }
}
