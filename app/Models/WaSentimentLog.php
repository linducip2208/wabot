<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaSentimentLog extends Model
{
    protected $fillable = [
        'user_id', 'contact_id', 'channel', 'message_id', 'message_text',
        'sentiment', 'confidence', 'raw_response',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
        'raw_response' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(WaMessage::class, 'message_id');
    }
}
