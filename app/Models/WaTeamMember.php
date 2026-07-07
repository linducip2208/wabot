<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class WaTeamMember extends Authenticatable
{
    protected $fillable = [
        'user_id', 'name', 'email', 'password', 'role',
        'is_active', 'max_concurrent',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WaConversationAssignment::class, 'team_member_id');
    }

    public function activeConversations(): HasMany
    {
        return $this->hasMany(WaConversationAssignment::class, 'team_member_id')
            ->where('status', 'active');
    }

    public function canTakeMore(): bool
    {
        return $this->activeConversations()->count() < $this->max_concurrent;
    }
}
