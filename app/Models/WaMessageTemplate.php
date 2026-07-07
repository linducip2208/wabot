<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaMessageTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'message', 'format',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
