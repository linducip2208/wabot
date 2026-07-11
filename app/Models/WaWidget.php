<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaWidget extends Model
{
    protected $fillable = [
        'user_id', 'name', 'greeting_message', 'offline_message',
        'theme_color', 'position', 'button_icon', 'channels',
        'is_active', 'embed_key',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (WaWidget $widget) {
            if (empty($widget->embed_key)) {
                $widget->embed_key = bin2hex(random_bytes(16));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(WaWidgetLead::class);
    }
}
