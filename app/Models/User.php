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

#[Fillable(['name', 'email', 'password', 'role', 'role_id', 'plan_id', 'trial_ends_at', 'expires_at', 'language_id', 'credits_balance', 'referral_code', 'referred_by_user_id'])]
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
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        if ($this->role_id) {
            if ($this->relationLoaded('role')) {
                return $this->role->name === self::ROLE_ADMIN;
            }

            return \App\Models\Role::find($this->role_id)?->name === self::ROLE_ADMIN;
        }

        return false;
    }

    public function isUser(): bool
    {
        if ($this->role === self::ROLE_USER) {
            return true;
        }

        if ($this->role_id) {
            if ($this->relationLoaded('role')) {
                return $this->role->name === self::ROLE_USER;
            }

            return \App\Models\Role::find($this->role_id)?->name === self::ROLE_USER;
        }

        return false;
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
        if ($this->expires_at && $this->expires_at->isPast()) {
            return null;
        }

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
            'expires_at' => 'datetime',
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

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function waWidgets(): HasMany
    {
        return $this->hasMany(WaWidget::class);
    }

    public function waStoreIntegrations(): HasMany
    {
        return $this->hasMany(WaStoreIntegration::class);
    }

    public function waSheetsIntegrations(): HasMany
    {
        return $this->hasMany(WaSheetsIntegration::class);
    }

    public function waAiTemplates(): HasMany
    {
        return $this->hasMany(WaAiTemplate::class);
    }

    public function waAiImageJobs(): HasMany
    {
        return $this->hasMany(WaAiImageJob::class);
    }

    public function waAiContentPlans(): HasMany
    {
        return $this->hasMany(WaAiContentPlan::class);
    }
}

    public function waSocialAccounts(): HasMany
    {
        return $this->hasMany(WaSocialAccount::class);
    }

    public function waPosts(): HasMany
    {
        return $this->hasMany(WaPost::class);
    }

    public function waPostCampaigns(): HasMany
    {
        return $this->hasMany(WaPostCampaign::class);
    }

    public function waPostLabels(): HasMany
    {
        return $this->hasMany(WaPostLabel::class);
    }

    public function waCaptionLibraries(): HasMany
    {
        return $this->hasMany(WaCaptionLibrary::class);
    }

    public function waRssSchedules(): HasMany
    {
        return $this->hasMany(WaRssSchedule::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by_user_id');
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(WaCreditTransaction::class);
    }

    public function affiliateCommissions(): HasMany
    {
        return $this->hasMany(WaAffiliateCommission::class);
    }

    public function affiliateWithdrawals(): HasMany
    {
        return $this->hasMany(WaAffiliateWithdrawal::class);
    }
}
