<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WaServer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'host', 'port', 'api_key', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(WaSession::class, 'server_id');
    }

    public function baseUrl(): string
    {
        $host = rtrim($this->host, '/');
        if (!str_starts_with($host, 'http')) {
            $host = 'http://' . $host;
        }
        return $host . ':' . $this->port;
    }
}
