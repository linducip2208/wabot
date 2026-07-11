<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAiContentPlan extends Model
{
    protected $fillable = [
        'user_id', 'name', 'platforms', 'topics', 'frequency',
        'start_date', 'end_date', 'status', 'calendar_data',
    ];

    protected $casts = [
        'platforms' => 'json',
        'topics' => 'json',
        'calendar_data' => 'json',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
