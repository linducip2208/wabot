<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaSession extends Model
{
    protected $fillable = [
        'user_id', 'server_id', 'meta_account_id', 'session_id', 'name', 'phone',
        'channel', 'status', 'qr_code', 'is_active', 'last_active_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(WaServer::class, 'server_id');
    }

    public function metaAccount(): BelongsTo
    {
        return $this->belongsTo(WaMetaAccount::class, 'meta_account_id');
    }

    public function autoreplies(): HasMany
    {
        return $this->hasMany(WaAutoreply::class, 'session_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(WaCampaign::class, 'session_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WaMessage::class, 'session_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WaSessionLog::class, 'session_id');
    }

    public function recurrings(): HasMany
    {
        return $this->hasMany(WaRecurring::class, 'session_id');
    }
}
