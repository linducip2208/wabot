<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaCommerceItem extends Model
{
    protected $fillable = [
        'order_id', 'catalog_item_id', 'name', 'qty', 'price', 'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(WaCommerceOrder::class, 'order_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(WaCatalogItem::class, 'catalog_item_id');
    }
}
