<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaEmailTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'subject', 'body_html', 'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
