<?php

namespace App\Http\Controllers;

use App\Models\WaIntentConfig;
use App\Models\WaAiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IntentConfigController extends Controller
{
    public function index()
    {
        $intents = WaIntentConfig::where('user_id', Auth::id())
            ->with('aiKey')
            ->latest()
            ->get();

        $aiKeys = WaAiKey::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('intents.index', compact('intents', 'aiKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'intent_label' => 'required|string|max:100',
            'keywords' => 'required|string|max:1000',
            'auto_reply' => 'nullable|string',
            'ai_key_id' => 'nullable|integer|exists:wa_ai_keys,id',
        ]);

        if (!empty($validated['ai_key_id'])) {
            $aiKey = WaAiKey::findOrFail($validated['ai_key_id']);
            abort_if($aiKey->user_id !== Auth::id(), 403);
        }

        WaIntentConfig::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'intent_label' => $validated['intent_label'],
            'keywords' => $validated['keywords'],
            'auto_reply' => $validated['auto_reply'] ?? '',
            'ai_key_id' => $validated['ai_key_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Intent Config berhasil disimpan.');
    }

    public function update(Request $request, WaIntentConfig $intent)
    {
        abort_if($intent->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'intent_label' => 'required|string|max:100',
            'keywords' => 'required|string|max:1000',
            'auto_reply' => 'nullable|string',
            'ai_key_id' => 'nullable|integer|exists:wa_ai_keys,id',
        ]);

        if (!empty($validated['ai_key_id'])) {
            $aiKey = WaAiKey::findOrFail($validated['ai_key_id']);
            abort_if($aiKey->user_id !== Auth::id(), 403);
        }

        $intent->update([
            'name' => $validated['name'],
            'intent_label' => $validated['intent_label'],
            'keywords' => $validated['keywords'],
            'auto_reply' => $validated['auto_reply'] ?? '',
            'ai_key_id' => $validated['ai_key_id'] ?? null,
        ]);

        return back()->with('success', 'Intent Config berhasil diperbarui.');
    }

    public function destroy(WaIntentConfig $intent)
    {
        abort_if($intent->user_id !== Auth::id(), 403);
        $intent->delete();

        return back()->with('success', 'Intent Config dihapus.');
    }
}
