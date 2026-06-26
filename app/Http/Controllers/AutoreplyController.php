<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AutoreplyController extends Controller
{
    public function index()
    {
        $autoreplies = WaAutoreply::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->get();

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('autoreplies.index', compact('autoreplies', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|exists:wa_sessions,id',
            'keyword' => 'required|string|max:255',
            'reply_message' => 'required|string|max:5000',
            'match_type' => 'required|in:exact,contains,starts_with',
        ]);

        WaAutoreply::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'] ?: null,
            'keyword' => $validated['keyword'],
            'reply_message' => $validated['reply_message'],
            'match_type' => $validated['match_type'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Auto-reply berhasil ditambahkan.');
    }

    public function update(Request $request, WaAutoreply $autoreply)
    {
        abort_if($autoreply->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'session_id' => 'nullable|exists:wa_sessions,id',
            'keyword' => 'required|string|max:255',
            'reply_message' => 'required|string|max:5000',
            'match_type' => 'required|in:exact,contains,starts_with',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['session_id'] = $validated['session_id'] ?: null;

        $autoreply->update($validated);

        return back()->with('success', 'Auto-reply diperbarui.');
    }

    public function destroy(WaAutoreply $autoreply)
    {
        abort_if($autoreply->user_id !== Auth::id(), 403);
        $autoreply->delete();

        return back()->with('success', 'Auto-reply dihapus.');
    }
}
