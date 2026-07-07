<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaDeal extends Model
{
    protected $fillable = [
        'user_id', 'contact_id', 'stage_id', 'title', 'value',
        'notes', 'expected_close_date', 'closed_at', 'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expected_close_date' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WaDealStage::class, 'stage_id');
    }
}
