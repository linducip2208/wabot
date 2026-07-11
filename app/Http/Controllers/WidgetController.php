<?php

namespace App\Http\Controllers;

use App\Models\WaWidget;
use App\Models\WaWidgetLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;

class WidgetController extends Controller
{
    public function index()
    {
        $widgets = WaWidget::where('user_id', auth()->id())->latest()->get();
        return view('widgets.index', compact('widgets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'greeting_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'button_icon' => 'nullable|string|max:50',
            'channels' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->filled('channels') && is_string($request->channels)) {
            $validated['channels'] = json_decode($request->channels, true);
        }

        $widget = WaWidget::create($validated);

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_created'));
    }

    public function update(Request $request, WaWidget $widget)
    {
        if ($widget->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'greeting_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'button_icon' => 'nullable|string|max:50',
            'channels' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('channels') && is_string($request->channels)) {
            $validated['channels'] = json_decode($request->channels, true);
        }

        $widget->update($validated);

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_updated'));
    }

    public function destroy(WaWidget $widget)
    {
        if ($widget->user_id !== auth()->id()) {
            abort(403);
        }

        $widget->delete();

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_deleted'));
    }

    public function embedScript($embedKey)
    {
        $widget = WaWidget::where('embed_key', $embedKey)->where('is_active', true)->firstOrFail();

        $channels = $widget->channels ?? [];

        $js = view('widgets.embed', [
            'widget' => $widget,
            'channels' => $channels,
        ])->render();

        return Response::make($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function storeLead(Request $request, $embedKey)
    {
        $widget = WaWidget::where('embed_key', $embedKey)->where('is_active', true)->firstOrFail();

        $key = 'widget-lead:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }
        RateLimiter::hit($key, 300);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $lead = $widget->leads()->create([
            'name' => $validated['name'],
            'message' => $validated['message'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'id' => $lead->id]);
    }
}
