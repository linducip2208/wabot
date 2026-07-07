<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaKnowledge extends Model
{
    protected $table = 'wa_knowledge';

    protected $fillable = [
        'user_id', 'title', 'content', 'type', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Decode content JSON → array of rows with question/answer/category.
     */
    public function getRowsAttribute(): array
    {
        return json_decode($this->content, true)['rows'] ?? [];
    }
}
