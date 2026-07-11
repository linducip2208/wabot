<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'code', 'driver', 'account_number', 'account_holder',
        'instructions', 'api_key', 'api_secret', 'logo_color', 'is_active',
        'is_auto', 'sort_order', 'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_auto' => 'boolean',
        'meta' => 'json',
    ];

    protected $hidden = [
        'api_key', 'api_secret',
    ];

    public function getApiKeyAttribute($value): ?string
    {
        if (!$value) return null;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute($value): ?string
    {
        if (!$value) return null;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setApiSecretAttribute($value): void
    {
        $this->attributes['api_secret'] = $value ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    public function isStripe(): bool
    {
        return $this->driver === 'stripe';
    }

    public function isRazorpay(): bool
    {
        return $this->driver === 'razorpay';
    }

    public function isManual(): bool
    {
        return !$this->driver || $this->driver === 'manual';
    }
}
