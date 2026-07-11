<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaMetaAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'waba_id', 'phone_number_id', 'phone_number',
        'access_token', 'app_id', 'app_secret', 'webhook_verify_token',
        'api_version', 'status', 'is_active', 'last_active_at', 'business_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WaSession::class, 'meta_account_id');
    }

    public function baseUrl(): string
    {
        return "https://graph.facebook.com/{$this->api_version}";
    }

    public function phoneNumberUrl(): string
    {
        return "{$this->baseUrl()}/{$this->phone_number_id}";
    }
}
