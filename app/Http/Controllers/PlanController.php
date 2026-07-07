<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
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
            return back()->with('error', 'Paket tidak tersedia.');
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

            $user->update([
                'plan_id' => $plan->id,
                'trial_ends_at' => null,
            ]);

            return redirect()->route('payment.page', $subscription);
        }

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $user->update([
            'plan_id' => $plan->id,
            'trial_ends_at' => null,
        ]);

        return back()->with('success', "Paket {$plan->name} diaktifkan.");
    }

    public function payment(Subscription $subscription)
    {
        abort_if($subscription->user_id !== Auth::id(), 403);

        $gateways = PaymentGateway::where('is_active', true)->orderBy('sort_order')->get();
        $plan = $subscription->plan;

        return view('payment.page', compact('subscription', 'gateways', 'plan'));
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
            'gateway_meta' => ['gateway_name' => $gateway->name],
        ]);

        $subscription->update(['status' => 'active']);

        Auth::user()->update(['plan_id' => $subscription->plan_id]);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Pembayaran berhasil dikonfirmasi. Paket Anda aktif.');
    }
}
