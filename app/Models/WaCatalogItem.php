<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCatalogItem extends Model
{
    protected $fillable = [
        'catalog_id', 'name', 'description', 'price', 'image_url',
        'product_code', 'stock', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(WaCatalog::class, 'catalog_id');
    }

    public function commerceItems(): HasMany
    {
        return $this->hasMany(WaCommerceItem::class, 'catalog_item_id');
    }
}
