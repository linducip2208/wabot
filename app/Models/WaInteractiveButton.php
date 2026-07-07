<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaInteractiveButton extends Model
{
    protected $fillable = [
        'user_id', 'name', 'header_type', 'header_text', 'header_media_url',
        'body_text', 'footer_text', 'buttons', 'session_id', 'is_template',
    ];

    protected $casts = [
        'buttons' => 'json',
        'is_template' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }
}
