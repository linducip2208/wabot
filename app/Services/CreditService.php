<?php

namespace App\Services;

use App\Models\User;
use App\Models\WaCreditPack;
use App\Models\WaCreditTransaction;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditService
{
    public function getBalance(User $user): int
    {
        return (int) $user->credits_balance;
    }

    public function hasCredits(User $user, int $amount): bool
    {
        return $this->getBalance($user) >= $amount;
    }

    public function addCredits(User $user, int $amount, string $description = '', string $referenceType = null, int $referenceId = null, string $type = 'admin_grant'): WaCreditTransaction
    {
        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId, $type) {
            $user->increment('credits_balance', $amount);
            $user->refresh();

            return WaCreditTransaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $user->credits_balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    public function deductCredits(User $user, int $amount, string $description = '', string $referenceType = null, int $referenceId = null): WaCreditTransaction
    {
        if (!$this->hasCredits($user, $amount)) {
            throw new RuntimeException(__('credits.insufficient_credits', ['required' => $amount, 'balance' => (int) $user->credits_balance]));
        }

        return DB::transaction(function () use ($user, $amount, $description, $referenceType, $referenceId) {
            $user->decrement('credits_balance', $amount);
            $user->refresh();

            return WaCreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'usage',
                'amount' => -$amount,
                'balance_after' => $user->credits_balance,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
            ]);
        });
    }

    public function purchasePack(User $user, int $packId): array
    {
        $pack = WaCreditPack::where('is_active', true)->findOrFail($packId);

        return DB::transaction(function () use ($user, $pack) {
            if ($pack->price <= 0) {
                $txn = $this->addCredits($user, $pack->credits, __('credits.pack_purchased', ['name' => $pack->name]), WaCreditPack::class, $pack->id, 'purchase');
                return [
                    'success' => true,
                    'transaction' => $txn,
                    'pack' => $pack,
                    'redirect_to_payment' => false,
                ];
            }

            $payment = PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => null,
                'type' => 'credit_pack',
                'amount' => $pack->price,
                'status' => 'pending',
                'gateway' => 'manual',
                'gateway_meta' => ['pack_id' => $pack->id, 'pack_name' => $pack->name, 'credits' => $pack->credits],
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'pack' => $pack,
                'redirect_to_payment' => true,
            ];
        });
    }

    public function confirmPackPayment(PaymentTransaction $payment): void
    {
        if ($payment->status !== 'pending') return;

        $meta = $payment->gateway_meta;
        $packId = $meta['pack_id'] ?? null;
        $pack = $packId ? WaCreditPack::find($packId) : null;
        $credits = $pack ? $pack->credits : (int) ($meta['credits'] ?? 0);

        DB::transaction(function () use ($payment, $credits, $pack) {
            $payment->update(['status' => 'completed']);
            $user = $payment->user;
            $this->addCredits($user, $credits, __('credits.pack_purchased', ['name' => $pack?->name ?? 'Paket Kredit']), PaymentTransaction::class, $payment->id, 'purchase');
        });
    }

    public function getTransactionHistory(User $user, int $limit = 50)
    {
        return $user->creditTransactions()->latest()->limit($limit)->get();
    }
}
