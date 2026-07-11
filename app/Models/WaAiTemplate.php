<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAiTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'prompt_template', 'category', 'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
