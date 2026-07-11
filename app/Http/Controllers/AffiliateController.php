<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    protected AffiliateService $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    public function index()
    {
        $user = Auth::user();
        $referralCode = $this->affiliateService->ensureReferralCode($user);
        $referralLink = route('register', ['ref' => $referralCode]);
        $summary = $this->affiliateService->getCommissionsSummary($user);
        $commissions = $user->affiliateCommissions()->with('referredUser')->latest()->limit(50)->get();
        $withdrawals = $this->affiliateService->getWithdrawals($user);

        return view('affiliate.index', compact('referralCode', 'referralLink', 'summary', 'commissions', 'withdrawals'));
    }

    public function requestWithdrawal(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_method' => 'required|string|in:bank_transfer,paypal,ewallet',
            'payment_details' => 'required|string|max:500',
        ]);

        try {
            $withdrawal = $this->affiliateService->requestWithdrawal(
                Auth::user(),
                $validated['amount'],
                $validated['payment_method'],
                $validated['payment_details']
            );

            return back()->with('success', __('affiliate.withdrawal_requested'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdrawalHistory()
    {
        $withdrawals = $this->affiliateService->getWithdrawals(Auth::user());
        return view('affiliate.withdrawals', compact('withdrawals'));
    }
}
