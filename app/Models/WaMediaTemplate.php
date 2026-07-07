<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaMediaTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'media_url', 'caption',
        'filename', 'mime_type', 'file_size', 'latitude', 'longitude',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function autoreplies(): HasMany
    {
        return $this->hasMany(WaAutoreply::class, 'media_template_id');
    }

    public function isMedia(): bool
    {
        return in_array($this->type, ['image', 'video', 'audio', 'document', 'sticker']);
    }

    public function isLocation(): bool
    {
        return $this->type === 'location';
    }
}
