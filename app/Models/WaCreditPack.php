<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaCreditPack extends Model
{
    protected $fillable = [
        'name', 'credits', 'price', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];
}
