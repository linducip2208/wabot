<?php

namespace App\Http\Controllers;

use App\Models\WaCoupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function validate(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $coupon = WaCoupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json(['valid' => false, 'message' => __('coupons.not_found')]);
        }

        if (!$coupon->isValid()) {
            return response()->json(['valid' => false, 'message' => __('coupons.invalid')]);
        }

        return response()->json([
            'valid' => true,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => (float) $coupon->discount_value,
                'min_order' => (float) $coupon->min_order,
            ],
        ]);
    }

    public function apply(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $coupon = WaCoupon::where('code', strtoupper($request->code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return back()->with('error', __('coupons.invalid'));
        }

        session(['applied_coupon' => [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount_value' => $coupon->discount_value,
            'min_order' => $coupon->min_order,
        ]]);

        return back()->with('success', __('coupons.applied', ['code' => $coupon->code]));
    }
}
