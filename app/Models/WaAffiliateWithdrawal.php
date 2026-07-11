<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAffiliateWithdrawal extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'payment_method',
        'payment_details', 'status', 'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
