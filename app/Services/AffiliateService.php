<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaAffiliateCommission;
use App\Models\WaAffiliateWithdrawal;
use App\Models\PaymentTransaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AffiliateService
{
    const DEFAULT_COMMISSION_RATE = 20.00;
    const MIN_WITHDRAWAL = 10000;

    public function generateReferralCode(User $user): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);
        return $code;
    }

    public function ensureReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }
        return $this->generateReferralCode($user);
    }

    public function captureReferral(string $code, User $newUser): bool
    {
        $referrer = User::where('referral_code', strtoupper($code))->first();
        if (!$referrer || $referrer->id === $newUser->id) {
            return false;
        }

        $newUser->update(['referred_by_user_id' => $referrer->id]);
        return true;
    }

    public function calculateCommission(float $transactionAmount, ?float $rate = null): float
    {
        $rate = $rate ?? self::DEFAULT_COMMISSION_RATE;
        return round($transactionAmount * ($rate / 100), 2);
    }

    public function getCommissionRate(User $referrer): float
    {
        $plan = $referrer->plan;
        if ($plan && $plan->can_affiliate && $plan->affiliate_commission_rate) {
            return (float) $plan->affiliate_commission_rate;
        }
        return self::DEFAULT_COMMISSION_RATE;
    }

    public function createCommission(User $referrer, User $referredUser, PaymentTransaction $transaction): ?WaAffiliateCommission
    {
        if (!$referrer->plan || !$referrer->plan->can_affiliate) {
            return null;
        }

        $rate = $this->getCommissionRate($referrer);
        $amount = $this->calculateCommission($transaction->amount, $rate);

        return WaAffiliateCommission::create([
            'user_id' => $referrer->id,
            'referred_user_id' => $referredUser->id,
            'transaction_id' => $transaction->id,
            'amount' => $amount,
            'rate' => $rate,
            'status' => 'pending',
        ]);
    }

    public function getCommissionsSummary(User $user): array
    {
        $commissions = $user->affiliateCommissions();
        return [
            'total_referrals' => $user->referrals()->count(),
            'total_commissions' => $commissions->sum('amount'),
            'pending' => $commissions->where('status', 'pending')->sum('amount'),
            'paid' => $commissions->where('status', 'paid')->sum('amount'),
            'available_balance' => $commissions->where('status', 'pending')->sum('amount'),
        ];
    }

    public function requestWithdrawal(User $user, float $amount, string $method, string $details): WaAffiliateWithdrawal
    {
        $summary = $this->getCommissionsSummary($user);

        if ($amount < self::MIN_WITHDRAWAL) {
            throw new \RuntimeException(__('affiliate.min_withdrawal', ['min' => number_format(self::MIN_WITHDRAWAL, 0, ',', '.')]));
        }

        $pendingTotal = (float) $summary['available_balance'];
        if ($amount > $pendingTotal) {
            throw new \RuntimeException(__('affiliate.insufficient_balance'));
        }

        return WaAffiliateWithdrawal::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $method,
            'payment_details' => $details,
            'status' => 'pending',
        ]);
    }

    public function getWithdrawals(User $user, int $limit = 20)
    {
        return $user->affiliateWithdrawals()->latest()->limit($limit)->get();
    }

    public function approveWithdrawal(WaAffiliateWithdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal->update([
                'status' => 'approved',
                'processed_at' => now(),
            ]);

            WaAffiliateCommission::where('user_id', $withdrawal->user_id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
        });
    }

    public function rejectWithdrawal(WaAffiliateWithdrawal $withdrawal): void
    {
        $withdrawal->update([
            'status' => 'rejected',
            'processed_at' => now(),
        ]);
    }
}
