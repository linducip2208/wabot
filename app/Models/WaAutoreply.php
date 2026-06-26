<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaAutoreply extends Model
{
    protected $fillable = [
        'user_id', 'session_id', 'keyword', 'reply_message',
        'match_type', 'is_active',
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

    public function matches(string $incomingMessage): bool
    {
        $incoming = mb_strtolower(trim($incomingMessage));
        $keyword = mb_strtolower(trim($this->keyword));

        return match ($this->match_type) {
            'exact' => $incoming === $keyword,
            'starts_with' => str_starts_with($incoming, $keyword),
            'contains' => str_contains($incoming, $keyword),
        };
    }
}
