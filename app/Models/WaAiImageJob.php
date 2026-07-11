<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAiImageJob extends Model
{
    protected $fillable = [
        'user_id', 'prompt', 'style', 'size', 'count', 'status', 'results',
    ];

    protected $casts = [
        'results' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
