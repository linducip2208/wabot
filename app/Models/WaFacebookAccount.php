<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaFacebookAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'page_id', 'page_name',
        'page_token_encrypted', 'app_secret_encrypted',
        'status', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'connected_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPageTokenAttribute(): ?string
    {
        if (empty($this->page_token_encrypted)) {
            return null;
        }
        try {
            return decrypt($this->page_token_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setPageTokenAttribute(?string $value): void
    {
        $this->attributes['page_token_encrypted'] = $value ? encrypt($value) : null;
    }

    public function getAppSecretAttribute(): ?string
    {
        if (empty($this->app_secret_encrypted)) {
            return null;
        }
        try {
            return decrypt($this->app_secret_encrypted);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setAppSecretAttribute(?string $value): void
    {
        $this->attributes['app_secret_encrypted'] = $value ? encrypt($value) : null;
    }
}
