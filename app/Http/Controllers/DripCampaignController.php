<?php

namespace App\Http\Controllers;

use App\Models\WaDripCampaign;
use App\Models\WaDripStep;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DripCampaignController extends Controller
{
    public function index()
    {
        $campaigns = WaDripCampaign::where('user_id', Auth::id())
            ->with('session')
            ->withCount('steps')
            ->latest()
            ->get();

        return view('drips.index', compact('campaigns'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('drips.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:wa_sessions,id',
        ]);

        WaDripCampaign::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'is_active' => false,
            'send_to_new_only' => true,
        ]);

        return redirect()->route('drips.index')->with('success', __('messages.success.drip_created'));
    }

    public function edit(WaDripCampaign $dripCampaign)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('drips.edit', compact('dripCampaign', 'sessions'));
    }

    public function update(Request $request, WaDripCampaign $dripCampaign)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:wa_sessions,id',
            'is_active' => 'boolean',
            'send_to_new_only' => 'boolean',
        ]);

        $wasActive = $dripCampaign->is_active;
        $nowActive = $request->boolean('is_active', false);

        $dripCampaign->update([
            'name' => $validated['name'],
            'session_id' => $validated['session_id'],
            'is_active' => $nowActive,
            'send_to_new_only' => $request->boolean('send_to_new_only', true),
            'activated_at' => (!$wasActive && $nowActive) ? now() : $dripCampaign->activated_at,
        ]);

        return back()->with('success', __('messages.success.drip_updated'));
    }

    public function destroy(WaDripCampaign $dripCampaign)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);
        $dripCampaign->delete();

        return back()->with('success', __('messages.success.drip_deleted'));
    }

    public function steps(WaDripCampaign $dripCampaign)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);

        $dripCampaign->load(['steps.aiKey', 'session']);
        $aiKeys = \App\Models\WaAiKey::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('drips.steps', compact('dripCampaign', 'aiKeys'));
    }

    public function stepsStore(Request $request, WaDripCampaign $dripCampaign)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'step_order' => 'required|integer|min:1',
            'wait_hours' => 'required|integer|min:0',
            'message' => 'required|string|max:5000',
            'media_url' => 'nullable|url|max:1000',
            'ai_key_id' => 'nullable|exists:wa_ai_keys,id',
        ]);

        WaDripStep::create([
            'drip_campaign_id' => $dripCampaign->id,
            'step_order' => $validated['step_order'],
            'wait_hours' => $validated['wait_hours'],
            'message' => $validated['message'],
            'media_url' => $validated['media_url'] ?? null,
            'ai_key_id' => $validated['ai_key_id'] ?? null,
        ]);

        return back()->with('success', __('messages.success.step_added'));
    }

    public function stepsDestroy(WaDripCampaign $dripCampaign, WaDripStep $step)
    {
        abort_if($dripCampaign->user_id !== Auth::id(), 403);
        abort_if($step->drip_campaign_id !== $dripCampaign->id, 404);

        $step->delete();

        return back()->with('success', __('messages.success.step_deleted'));
    }
}
