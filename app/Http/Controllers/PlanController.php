<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\WaCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $currentPlan = Auth::user()->plan;

        return view('plans.index', compact('plans', 'currentPlan'));
    }

    public function subscribe(Request $request, Plan $plan)
    {
        if (!$plan->is_active) {
            return back()->with('error', __('messages.error.plan_unavailable'));
        }

        $user = Auth::user();

        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired', 'canceled_at' => now()]);

        if ($plan->price > 0) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'pending',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            return redirect()->route('payment.page', $subscription);
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => null,
        ]);

        $user->update([
            'plan_id' => $plan->id,
            'trial_ends_at' => null,
            'expires_at' => null,
        ]);

        return back()->with('success', __('messages.success.plan_activated', ['name' => $plan->name]));
    }

    public function payment(Subscription $subscription)
    {
        abort_if($subscription->user_id !== Auth::id(), 403);

        $gateways = PaymentGateway::where('is_active', true)->orderBy('sort_order')->get();
        $plan = $subscription->plan;
        $appliedCoupon = session('applied_coupon');
        $discountAmount = 0;
        $finalPrice = $plan->price;

        if ($appliedCoupon) {
            $coupon = WaCoupon::find($appliedCoupon['id']);
            if ($coupon && $coupon->isValid()) {
                if (empty($coupon->plan_id) || $coupon->plan_id === $plan->id) {
                    if ($plan->price >= $coupon->min_order) {
                        $discountAmount = $coupon->calculateDiscount($plan->price);
                        $finalPrice = max(0, $plan->price - $discountAmount);
                    }
                }
            }
        }

        return view('payment.page', compact('subscription', 'gateways', 'plan', 'appliedCoupon', 'discountAmount', 'finalPrice'));
    }

    public function uploadPayment(Request $request, Subscription $subscription)
    {
        abort_if($subscription->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'gateway_id' => 'required|exists:payment_gateways,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $gateway = PaymentGateway::findOrFail($data['gateway_id']);

        PaymentTransaction::create([
            'user_id' => Auth::id(),
            'subscription_id' => $subscription->id,
            'type' => 'subscription',
            'amount' => $data['amount'],
            'status' => 'pending',
            'gateway' => $gateway->code,
            'gateway_meta' => [
                'gateway_name' => $gateway->name,
                'coupon_code' => session('applied_coupon.code') ?? null,
            ],
        ]);

        $subscription->update(['status' => 'active']);

        Auth::user()->update(['plan_id' => $subscription->plan_id]);

        $appliedCoupon = session('applied_coupon');
        if ($appliedCoupon) {
            WaCoupon::where('id', $appliedCoupon['id'])->increment('used_count');
            session()->forget('applied_coupon');
        }

        return redirect()->route('subscriptions.index')
            ->with('success', __('messages.success.payment_confirmed'));
    }
}
