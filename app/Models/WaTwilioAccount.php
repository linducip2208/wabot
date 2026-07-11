<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class WaTwilioAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'account_sid_encrypted', 'auth_token_encrypted',
        'phone_number', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAccountSidAttribute(): ?string
    {
        return $this->account_sid_encrypted
            ? Crypt::decryptString($this->account_sid_encrypted)
            : null;
    }

    public function getAuthTokenAttribute(): ?string
    {
        return $this->auth_token_encrypted
            ? Crypt::decryptString($this->auth_token_encrypted)
            : null;
    }

    public function setAccountSidEncryptedAttribute($value): void
    {
        $this->attributes['account_sid_encrypted'] = Crypt::encryptString($value);
    }

    public function setAuthTokenEncryptedAttribute($value): void
    {
        $this->attributes['auth_token_encrypted'] = Crypt::encryptString($value);
    }

    public function baseUrl(): string
    {
        return "https://api.twilio.com/2010-04-01/Accounts/{$this->getAccountSidAttribute()}/Messages.json";
    }
}
