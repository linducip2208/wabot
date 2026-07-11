<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:plans,slug',
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly,lifetime',
            'max_sessions' => 'required|integer|min:0',
            'max_contacts' => 'required|integer|min:0',
            'max_autoreplies' => 'required|integer|min:0',
            'max_campaign_recipients' => 'required|integer|min:0',
            'max_meta_accounts' => 'nullable|integer|min:0',
            'max_forms' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $bools = ['can_manage_server','can_use_meta','can_use_forms','can_use_calling',
            'can_use_instagram','can_use_flow','can_use_ai_agent','can_use_intent',
            'can_use_drip','can_use_ab_test','can_use_catalog','can_use_commerce',
            'can_use_deals','can_use_kanban'];

        foreach ($bools as $b) {
            $validated[$b] = $request->boolean($b);
        }

        $validated['features'] = json_encode($request->input('features', []));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        Plan::create($validated);

        return redirect()->route('admin.plans.index')->with('success', __('messages.success.plan_created'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:100|unique:plans,slug,' . $plan->id,
            'price' => 'required|numeric|min:0',
            'billing_period' => 'required|in:monthly,yearly,lifetime',
            'max_sessions' => 'required|integer|min:0',
            'max_contacts' => 'required|integer|min:0',
            'max_autoreplies' => 'required|integer|min:0',
            'max_campaign_recipients' => 'required|integer|min:0',
            'max_meta_accounts' => 'nullable|integer|min:0',
            'max_forms' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $bools = ['can_manage_server','can_use_meta','can_use_forms','can_use_calling',
            'can_use_instagram','can_use_flow','can_use_ai_agent','can_use_intent',
            'can_use_drip','can_use_ab_test','can_use_catalog','can_use_commerce',
            'can_use_deals','can_use_kanban'];

        foreach ($bools as $b) {
            $validated[$b] = $request->boolean($b);
        }

        $validated['features'] = json_encode($request->input('features', []));
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? $plan->sort_order;

        $plan->update($validated);

        return back()->with('success', __('messages.success.plan_updated'));
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', __('messages.success.plan_deleted'));
    }
}
