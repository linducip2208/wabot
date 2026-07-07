<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaSlaLog extends Model
{
    protected $fillable = [
        'user_id', 'contact_id', 'sla_config_id', 'team_member_id',
        'customer_message_at', 'first_response_at', 'resolved_at',
        'first_response_breached', 'resolution_breached',
    ];

    protected $casts = [
        'customer_message_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'first_response_breached' => 'boolean',
        'resolution_breached' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function slaConfig(): BelongsTo
    {
        return $this->belongsTo(WaSlaConfig::class, 'sla_config_id');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(WaTeamMember::class, 'team_member_id');
    }
}
