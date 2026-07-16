<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCampaign extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'channel', 'meta_account_id', 'telegram_account_id',
        'instagram_account_id', 'facebook_account_id', 'gbm_account_id', 'discord_account_id',
        'tiktok_account_id', 'line_account_id', 'twitter_account_id',
        'twilio_account_id', 'sendgrid_account_id',
        'name', 'message', 'delay_seconds', 'delay_min_seconds', 'delay_max_seconds', 'media_url', 'message_type',
        'recipient_ids', 'status', 'total_recipients', 'sent_count', 'failed_count',
        'scheduled_at',
    ];

    protected $casts = [
        'recipient_ids' => 'json',
        'scheduled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }

    public function telegramAccount(): BelongsTo
    {
        return $this->belongsTo(WaTelegramAccount::class, 'telegram_account_id');
    }

    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(WaInstagramAccount::class, 'instagram_account_id');
    }

    public function facebookAccount(): BelongsTo
    {
        return $this->belongsTo(WaFacebookAccount::class, 'facebook_account_id');
    }

    public function gbmAccount(): BelongsTo
    {
        return $this->belongsTo(WaGbmAccount::class, 'gbm_account_id');
    }

    public function discordAccount(): BelongsTo
    {
        return $this->belongsTo(WaDiscordAccount::class, 'discord_account_id');
    }

    public function tiktokAccount(): BelongsTo
    {
        return $this->belongsTo(WaTiktokAccount::class, 'tiktok_account_id');
    }

    public function lineAccount(): BelongsTo
    {
        return $this->belongsTo(WaLineAccount::class, 'line_account_id');
    }

    public function twitterAccount(): BelongsTo
    {
        return $this->belongsTo(WaTwitterAccount::class, 'twitter_account_id');
    }

    public function twilioAccount(): BelongsTo
    {
        return $this->belongsTo(WaTwilioAccount::class, 'twilio_account_id');
    }

    public function sendgridAccount(): BelongsTo
    {
        return $this->belongsTo(WaSendGridAccount::class, 'sendgrid_account_id');
    }
}
