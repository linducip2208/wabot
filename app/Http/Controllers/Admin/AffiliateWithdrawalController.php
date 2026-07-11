<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaAffiliateWithdrawal;
use App\Services\AffiliateService;

class AffiliateWithdrawalController extends Controller
{
    protected AffiliateService $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    public function index()
    {
        $withdrawals = WaAffiliateWithdrawal::with('user')->latest()->get();
        return view('admin.affiliate-withdrawals.index', compact('withdrawals'));
    }

    public function approve(WaAffiliateWithdrawal $withdrawal)
    {
        $this->affiliateService->approveWithdrawal($withdrawal);
        return back()->with('success', __('admin.withdrawal_approved'));
    }

    public function reject(WaAffiliateWithdrawal $withdrawal)
    {
        $this->affiliateService->rejectWithdrawal($withdrawal);
        return back()->with('success', __('admin.withdrawal_rejected'));
    }
}
