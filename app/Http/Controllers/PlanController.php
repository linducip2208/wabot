<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
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

        // End previous subscription
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired', 'canceled_at' => now()]);

        if ($plan->price > 0) {
            // Paid plan - create subscription with payment needed
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
            ]);

            $user->update([
                'plan_id' => $plan->id,
                'trial_ends_at' => null,
            ]);

            return back()->with('success', "Berlangganan ke paket {$plan->name}. (Pembayaran akan diintegrasikan)");
        }

        // Free plan - activate immediately
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
}
