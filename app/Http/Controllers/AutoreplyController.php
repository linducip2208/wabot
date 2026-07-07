<?php

namespace App\Http\Controllers;

use App\Models\WaAiKey;
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

        $aiKeys = WaAiKey::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('autoreplies.index', compact('autoreplies', 'sessions', 'aiKeys'));
    }

    public function store(Request $request)
    {
        $isWelcome = $request->input('match_type') === 'welcome';
        $isFallback = $request->input('match_type') === 'fallback';
        $validated = $request->validate([
            'session_id' => 'nullable|exists:wa_sessions,id',
            'keyword' => ($isWelcome || $isFallback || $request->boolean('use_ai')) ? 'nullable|string|max:255' : 'required|string|max:255',
            'reply_message' => $request->boolean('use_ai') ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'match_type' => 'required|in:exact,contains,starts_with,welcome,fallback',
            'ai_key_id' => 'nullable|exists:wa_ai_keys,id',
            'use_ai' => 'boolean',
        ]);

        WaAutoreply::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'] ?: null,
            'keyword' => $isFallback ? ($request->input('fallback_cooldown', '5')) : ($validated['keyword'] ?? ''),
            'reply_message' => $validated['reply_message'] ?? '',
            'match_type' => $validated['match_type'],
            'ai_key_id' => $validated['ai_key_id'] ?? null,
            'use_ai' => $request->boolean('use_ai'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Auto-reply berhasil ditambahkan.');
    }

    public function update(Request $request, WaAutoreply $autoreply)
    {
        abort_if($autoreply->user_id !== Auth::id(), 403);

        $isWelcome = $request->input('match_type') === 'welcome';
        $isFallback = $request->input('match_type') === 'fallback';
        $validated = $request->validate([
            'session_id' => 'nullable|exists:wa_sessions,id',
            'keyword' => ($isWelcome || $isFallback || $request->boolean('use_ai')) ? 'nullable|string|max:255' : 'required|string|max:255',
            'reply_message' => $request->boolean('use_ai') ? 'nullable|string|max:5000' : 'required|string|max:5000',
            'match_type' => 'required|in:exact,contains,starts_with,welcome,fallback',
            'is_active' => 'boolean',
            'ai_key_id' => 'nullable|exists:wa_ai_keys,id',
            'use_ai' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['session_id'] = $validated['session_id'] ?: null;
        $validated['ai_key_id'] = $validated['ai_key_id'] ?? null;
        $validated['use_ai'] = $request->boolean('use_ai');
        $validated['keyword'] = $isFallback ? ($request->input('fallback_cooldown', '5')) : ($validated['keyword'] ?? '');
        $validated['reply_message'] = $validated['reply_message'] ?? '';

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
