<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAffiliateCommission extends Model
{
    protected $fillable = [
        'user_id', 'referred_user_id', 'transaction_id',
        'amount', 'rate', 'status', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class, 'transaction_id');
    }
}
