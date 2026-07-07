<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaCommerceOrder extends Model
{
    protected $fillable = [
        'user_id', 'contact_id', 'session_id', 'order_number',
        'total', 'status', 'payment_method', 'payment_proof_url',
        'paid_at', 'notes', 'shipping_address',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(WaCommerceItem::class, 'order_id');
    }
}
