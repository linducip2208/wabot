<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCaptionLibrary extends Model
{
    protected $fillable = [
        'user_id', 'name', 'content', 'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
