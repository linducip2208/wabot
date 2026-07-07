<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaConversationAssignment extends Model
{
    protected $fillable = [
        'contact_id', 'team_member_id', 'session_id',
        'assigned_at', 'closed_at', 'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WaContact::class, 'contact_id');
    }

    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(WaTeamMember::class, 'team_member_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(WaSession::class, 'session_id');
    }
}
