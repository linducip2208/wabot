<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlShortener extends Model
{
    protected $fillable = [
        'name', 'base_url', 'api_key_encrypted', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
