<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'native_name',
        'iso',
        'rtl',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'rtl' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function isRtl(): bool
    {
        return $this->rtl;
    }

    public function getFlagAttribute(): string
    {
        return match ($this->iso) {
            'id' => 'id',
            'en' => 'gb',
            'ja' => 'jp',
            'ko' => 'kr',
            'zh' => 'cn',
            'ar' => 'sa',
            default => strtolower($this->iso),
        };
    }
}
