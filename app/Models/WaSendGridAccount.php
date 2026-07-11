<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class WaSendGridAccount extends Model
{
    protected $table = 'wa_sendgrid_accounts';
    protected $fillable = [
        'user_id', 'name', 'api_key_encrypted',
        'from_email', 'from_name', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getApiKeyAttribute(): ?string
    {
        return $this->api_key_encrypted
            ? Crypt::decryptString($this->api_key_encrypted)
            : null;
    }

    public function setApiKeyEncryptedAttribute($value): void
    {
        $this->attributes['api_key_encrypted'] = Crypt::encryptString($value);
    }
}
