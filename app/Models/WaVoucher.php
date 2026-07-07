<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WaVoucher extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'plan_id', 'code', 'max_uses', 'used_count', 'duration_days', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public static function generate(): string
    {
        do {
            $code = strtoupper(Str::random(12));
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
