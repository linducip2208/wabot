<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaCatalog extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'session_id', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WaCatalogItem::class, 'catalog_id')->orderBy('sort_order');
    }
}
