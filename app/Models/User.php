<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'plan_id', 'trial_ends_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin(): bool
    {
        if ($this->role_id && $this->relationLoaded('role')) {
            return $this->role->name === self::ROLE_ADMIN;
        }

        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        if ($this->role_id && $this->relationLoaded('role')) {
            return $this->role->name === self::ROLE_USER;
        }

        return $this->role === self::ROLE_USER;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->relationLoaded('role') && $this->role) {
            return $this->role->hasPermission($permission);
        }

        if ($this->role_id) {
            return $this->role()->first()?->hasPermission($permission) ?? false;
        }

        return $this->isAdmin();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function waServers(): HasMany
    {
        return $this->hasMany(WaServer::class);
    }

    public function waSessions(): HasMany
    {
        return $this->hasMany(WaSession::class);
    }

    public function waAutoreplies(): HasMany
    {
        return $this->hasMany(WaAutoreply::class);
    }

    public function waContacts(): HasMany
    {
        return $this->hasMany(WaContact::class);
    }

    public function waCampaigns(): HasMany
    {
        return $this->hasMany(WaCampaign::class);
    }

    public function waMessages(): HasMany
    {
        return $this->hasMany(WaMessage::class);
    }

    public function waSessionLogs(): HasMany
    {
        return $this->hasMany(WaSessionLog::class);
    }

    public function waRecurrings(): HasMany
    {
        return $this->hasMany(WaRecurring::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function contactGroups(): HasMany
    {
        return $this->hasMany(ContactGroup::class);
    }

    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function waWebhooks(): HasMany
    {
        return $this->hasMany(WaWebhook::class);
    }

    public function waMessageTemplates(): HasMany
    {
        return $this->hasMany(WaMessageTemplate::class);
    }

    public function waAiKeys(): HasMany
    {
        return $this->hasMany(WaAiKey::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'author_id');
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }
}
