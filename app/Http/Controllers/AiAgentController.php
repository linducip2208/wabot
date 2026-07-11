<?php

namespace App\Http\Controllers;

use App\Models\WaAiAgent;
use App\Models\WaAiKey;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiAgentController extends Controller
{
    public function index()
    {
        $agents = WaAiAgent::where('user_id', Auth::id())
            ->with('aiKey')
            ->latest()
            ->get();

        $aiKeys = WaAiKey::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('ai-agents.index', compact('agents', 'aiKeys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_key_id' => 'required|integer|exists:wa_ai_keys,id',
            'role' => 'required|in:sales,support,billing,general',
            'personality_prompt' => 'nullable|string',
            'trigger_keywords' => 'nullable|string|max:500',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:whatsapp,meta,instagram,telegram',
        ]);

        $aiKey = WaAiKey::findOrFail($validated['ai_key_id']);
        abort_if($aiKey->user_id !== Auth::id(), 403);

        WaAiAgent::create([
            'user_id' => Auth::id(),
            'ai_key_id' => $validated['ai_key_id'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'personality_prompt' => $validated['personality_prompt'] ?? '',
            'trigger_keywords' => $validated['trigger_keywords'] ?? '',
            'channels' => $validated['channels'] ?? null,
            'is_active' => true,
            'is_default' => false,
        ]);

        return back()->with('success', __('messages.success.ai_agent_created'));
    }

    public function update(Request $request, WaAiAgent $agent)
    {
        abort_if($agent->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_key_id' => 'required|integer|exists:wa_ai_keys,id',
            'role' => 'required|in:sales,support,billing,general',
            'personality_prompt' => 'nullable|string',
            'trigger_keywords' => 'nullable|string|max:500',
            'channels' => 'nullable|array',
            'channels.*' => 'string|in:whatsapp,meta,instagram,telegram',
        ]);

        $aiKey = WaAiKey::findOrFail($validated['ai_key_id']);
        abort_if($aiKey->user_id !== Auth::id(), 403);

        $agent->update([
            'ai_key_id' => $validated['ai_key_id'],
            'name' => $validated['name'],
            'role' => $validated['role'],
            'personality_prompt' => $validated['personality_prompt'] ?? '',
            'trigger_keywords' => $validated['trigger_keywords'] ?? '',
            'channels' => $validated['channels'] ?? null,
        ]);

        return back()->with('success', __('messages.success.ai_agent_updated'));
    }

    public function destroy(WaAiAgent $agent)
    {
        abort_if($agent->user_id !== Auth::id(), 403);
        $agent->delete();

        return back()->with('success', __('messages.success.ai_agent_deleted'));
    }

    public function test(Request $request, WaAiAgent $agent)
    {
        abort_if($agent->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        if (!$agent->aiKey || !$agent->aiKey->is_active) {
            return back()->with('error', __('messages.error.ai_key_inactive'));
        }

        $aiService = app(AiService::class);
        $knowledgeContext = $aiService->getKnowledgeContext(Auth::id());

        $response = $aiService->send($agent->aiKey, $validated['message'], $knowledgeContext);

        if ($response === null) {
            return back()->with('error', __('messages.error.ai_response_failed'));
        }

        return back()->with('success', __('messages.success.ai_agent_responded'))
            ->with('test_response', $response)
            ->with('test_message', $validated['message']);
    }
}
