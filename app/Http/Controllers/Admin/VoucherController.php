<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\WaVoucher;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = WaVoucher::with('plan')->latest()->get();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.vouchers.index', compact('vouchers', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'max_uses' => 'nullable|integer|min:1',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        WaVoucher::create([
            'plan_id' => $data['plan_id'],
            'code' => WaVoucher::generate(),
            'max_uses' => $data['max_uses'] ?? 1,
            'used_count' => 0,
            'duration_days' => $data['duration_days'] ?? 30,
            'is_active' => true,
        ]);

        return back()->with('success', __('messages.success.voucher_created'));
    }

    public function destroy(WaVoucher $voucher)
    {
        $voucher->delete();
        return back()->with('success', __('messages.success.voucher_deleted'));
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $voucher = WaVoucher::where('code', strtoupper($request->code))->first();

        if (!$voucher) {
            return back()->with('error', __('messages.error.voucher_not_found'));
        }

        if (!$voucher->is_active) {
            return back()->with('error', __('messages.error.voucher_inactive'));
        }

        if ($voucher->used_count >= $voucher->max_uses) {
            return back()->with('error', __('messages.error.voucher_limit_reached'));
        }

        $user = Auth::user();
        $plan = $voucher->plan;

        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired', 'canceled_at' => now()]);

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($voucher->duration_days),
        ]);

        $user->update([
            'plan_id' => $plan->id,
            'trial_ends_at' => null,
        ]);

        $voucher->increment('used_count');

        return redirect()->route('subscriptions.index')
            ->with('success', __('messages.success.voucher_redeemed', ['name' => $plan->name, 'days' => $voucher->duration_days]));
    }
}
