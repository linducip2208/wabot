<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaCoupon;
use App\Models\Plan;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = WaCoupon::with('plan')->latest()->get();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.coupons.index', compact('coupons', 'plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:wa_coupons,code',
            'plan_id' => 'nullable|exists:plans,id',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
        ]);

        WaCoupon::create([
            'code' => strtoupper($data['code']),
            'plan_id' => $data['plan_id'] ?? null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'min_order' => $data['min_order'] ?? 0,
            'max_uses' => $data['max_uses'] ?? 0,
            'used_count' => 0,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', __('admin.coupon_created'));
    }

    public function toggle(WaCoupon $coupon)
    {
        $coupon->update(['is_active' => !$coupon->is_active]);
        return back()->with('success', __('admin.coupon_toggled'));
    }

    public function destroy(WaCoupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', __('admin.coupon_deleted'));
    }
}
