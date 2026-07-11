<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = null)
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $subscription = $user->activeSubscription();
        $plan = $subscription?->plan ?? $user->plan;

        if (!$plan) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('api.error.no_active_plan')], 402);
            }
            return redirect()->route('plans.index')
                ->with('warning', __('messages.warning.select_plan'));
        }

        if (!$subscription && $plan->price > 0) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('api.error.subscription_required')], 402);
            }
            return redirect()->route('plans.index')
                ->with('warning', __('messages.warning.subscription_required'));
        }

        if ($feature) {
            $limit = $plan->getLimit($feature);
            $current = match ($feature) {
                'sessions' => $user->waSessions()->count(),
                'contacts' => $user->waContacts()->count(),
                'autoreplies' => $user->waAutoreplies()->count(),
                default => 0,
            };

            if ($current >= $limit && $limit > 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => __('api.error.limit_reached', ['feature' => $feature, 'current' => $current, 'limit' => $limit]),
                    ], 402);
                }
                return back()->with('error', __('messages.error.limit_reached', ['feature' => $feature, 'current' => $current, 'limit' => $limit]));
            }
        }

        $request->attributes->set('plan_limit', $plan);
        return $next($request);
    }
}
