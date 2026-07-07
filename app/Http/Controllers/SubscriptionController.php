<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentPlan = $user->plan;
        $currentSubscription = $user->activeSubscription();

        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->get();

        $usage = [
            'sessions' => [
                'current' => $user->waSessions()->count(),
                'limit' => $currentPlan?->max_sessions ?? 0,
            ],
            'contacts' => [
                'current' => $user->waContacts()->count(),
                'limit' => $currentPlan?->max_contacts ?? 0,
            ],
            'autoreplies' => [
                'current' => $user->waAutoreplies()->count(),
                'limit' => $currentPlan?->max_autoreplies ?? 0,
            ],
        ];

        return view('subscriptions.index', compact(
            'currentPlan', 'currentSubscription', 'subscriptions', 'usage'
        ));
    }

    public function history()
    {
        $user = Auth::user();

        $subscriptions = $user->subscriptions()
            ->with('plan')
            ->latest()
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'plan' => $sub->plan?->name ?? 'Tanpa Paket',
                    'status' => $sub->status,
                    'starts_at' => $sub->starts_at?->format('d M Y H:i'),
                    'ends_at' => $sub->ends_at?->format('d M Y H:i'),
                    'is_active' => $sub->isActive(),
                ];
            });

        return response()->json($subscriptions);
    }
}
