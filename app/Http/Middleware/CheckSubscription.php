<?php

namespace App\Http\Middleware;

use App\Models\Plan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = null)
    {
        $user = Auth::user();
        if (!$user) return $next($request);

        $plan = $user->plan;

        if (!$plan) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No active plan.'], 402);
            }
            return redirect()->route('plans.index')
                ->with('warning', 'Silakan pilih paket untuk melanjutkan.');
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
                        'error' => "Limit {$feature} reached ({$current}/{$limit}). Upgrade plan.",
                    ], 402);
                }
                return back()->with('error', "Limit {$feature} tercapai ({$current}/{$limit}). Upgrade paket.");
            }
        }

        return $next($request);
    }
}
