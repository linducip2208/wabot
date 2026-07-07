<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaIntentConfig extends Model
{
    protected $fillable = [
        'user_id', 'name', 'intent_label', 'keywords',
        'ai_key_id', 'auto_reply', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiKey(): BelongsTo
    {
        return $this->belongsTo(WaAiKey::class, 'ai_key_id');
    }

    public function hasKeyword(string $message): bool
    {
        $keywordList = array_map('trim', explode(',', mb_strtolower($this->keywords)));
        $msg = mb_strtolower($message);
        foreach ($keywordList as $kw) {
            if ($kw && str_contains($msg, $kw)) return true;
        }
        return false;
    }
}
