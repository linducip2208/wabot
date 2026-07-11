<?php

namespace App\Http\Controllers;

use App\Models\WaInteractiveButton;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteractiveButtonController extends Controller
{
    public function index()
    {
        $buttons = WaInteractiveButton::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->get();

        return view('buttons.index', compact('buttons'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('buttons.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header_type' => 'required|string|in:text,image,video,document',
            'header_text' => 'nullable|string|max:255',
            'header_media_url' => 'nullable|url|max:1000',
            'body_text' => 'required|string|max:5000',
            'footer_text' => 'nullable|string|max:255',
            'buttons' => 'required|json',
            'session_id' => 'required|exists:wa_sessions,id',
        ]);

        $decoded = json_decode($request->input('buttons'), true);
        if (!is_array($decoded)) {
            return back()->with('error', __('messages.error.invalid_button_format'))->withInput();
        }

        WaInteractiveButton::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'header_type' => $validated['header_type'],
            'header_text' => $validated['header_text'] ?? null,
            'header_media_url' => $validated['header_media_url'] ?? null,
            'body_text' => $validated['body_text'],
            'footer_text' => $validated['footer_text'] ?? null,
            'buttons' => $validated['buttons'],
            'session_id' => $validated['session_id'],
        ]);

        return redirect()->route('buttons.index')->with('success', __('messages.success.button_created'));
    }

    public function edit(WaInteractiveButton $button)
    {
        abort_if($button->user_id !== Auth::id(), 403);

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('buttons.edit', compact('button', 'sessions'));
    }

    public function update(Request $request, WaInteractiveButton $button)
    {
        abort_if($button->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'header_type' => 'required|string|in:text,image,video,document',
            'header_text' => 'nullable|string|max:255',
            'header_media_url' => 'nullable|url|max:1000',
            'body_text' => 'required|string|max:5000',
            'footer_text' => 'nullable|string|max:255',
            'buttons' => 'required|json',
            'session_id' => 'required|exists:wa_sessions,id',
        ]);

        $decoded = json_decode($request->input('buttons'), true);
        if (!is_array($decoded)) {
            return back()->with('error', __('messages.error.invalid_button_format'))->withInput();
        }

        $button->update([
            'name' => $validated['name'],
            'header_type' => $validated['header_type'],
            'header_text' => $validated['header_text'] ?? null,
            'header_media_url' => $validated['header_media_url'] ?? null,
            'body_text' => $validated['body_text'],
            'footer_text' => $validated['footer_text'] ?? null,
            'buttons' => $validated['buttons'],
            'session_id' => $validated['session_id'],
        ]);

        return redirect()->route('buttons.index')->with('success', __('messages.success.button_updated'));
    }

    public function destroy(WaInteractiveButton $button)
    {
        abort_if($button->user_id !== Auth::id(), 403);
        $button->delete();

        return back()->with('success', __('messages.success.button_deleted'));
    }
}
