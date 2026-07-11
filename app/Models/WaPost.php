<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaPost extends Model
{
    protected $fillable = [
        'user_id', 'content', 'media_urls', 'platform_targets',
        'scheduled_at', 'published_at', 'status', 'result',
        'campaign_id', 'label_id',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'platform_targets' => 'array',
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'result' => 'array',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_PUBLISHED = 'published';
    const STATUS_FAILED = 'failed';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WaPostCampaign::class, 'campaign_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(WaPostLabel::class, 'label_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
