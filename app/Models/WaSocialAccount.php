<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaSocialAccount extends Model
{
    protected $fillable = [
        'user_id', 'platform', 'name', 'platform_id',
        'access_token', 'refresh_token', 'token_expires_at',
        'profile_data', 'is_active', 'connected_at',
    ];

    protected $casts = [
        'profile_data' => 'array',
        'is_active' => 'boolean',
        'token_expires_at' => 'datetime',
        'connected_at' => 'datetime',
    ];

    const PLATFORM_FACEBOOK_PAGE = 'facebook_page';
    const PLATFORM_INSTAGRAM_PROFESSIONAL = 'instagram_professional';
    const PLATFORM_LINKEDIN_PAGE = 'linkedin_page';
    const PLATFORM_TIKTOK = 'tiktok';
    const PLATFORM_X_TWITTER = 'x_twitter';

    public static function platforms(): array
    {
        return [
            self::PLATFORM_FACEBOOK_PAGE => 'Facebook Page',
            self::PLATFORM_INSTAGRAM_PROFESSIONAL => 'Instagram Professional',
            self::PLATFORM_LINKEDIN_PAGE => 'LinkedIn Page',
            self::PLATFORM_TIKTOK => 'TikTok',
            self::PLATFORM_X_TWITTER => 'X / Twitter',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(WaPost::class, 'social_account_id');
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return decrypt($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = $value ? encrypt($value) : null;
    }
}
