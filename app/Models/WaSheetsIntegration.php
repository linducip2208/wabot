<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class WaSheetsIntegration extends Model
{
    protected $table = 'wa_sheets_integrations';

    protected $fillable = [
        'user_id', 'name', 'spreadsheet_id', 'sheet_name',
        'service_account_json', 'sync_direction',
        'is_active', 'sync_status', 'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'service_account_json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getServiceAccountJsonAttribute($value): ?string
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public function setServiceAccountJsonAttribute($value): void
    {
        $this->attributes['service_account_json'] = $value ? Crypt::encryptString($value) : null;
    }
}
