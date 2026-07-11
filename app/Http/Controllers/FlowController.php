<?php

namespace App\Http\Controllers;

use App\Models\WaAiKey;
use App\Models\WaFlow;
use App\Models\WaFlowNode;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlowController extends Controller
{
    public function index()
    {
        $flows = WaFlow::where('user_id', Auth::id())
            ->withCount('nodes')
            ->latest()
            ->get();

        return view('flows.index', compact('flows'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('flows.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_keyword' => 'required|string|max:255',
            'trigger_match_type' => 'required|in:exact,contains,starts_with',
        ]);

        WaFlow::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_keyword' => $validated['trigger_keyword'],
            'trigger_match_type' => $validated['trigger_match_type'],
            'is_active' => true,
        ]);

        return redirect()->route('flows.index')->with('success', __('messages.success.flow_created'));
    }

    public function edit(WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('flows.edit', compact('flow', 'sessions'));
    }

    public function update(Request $request, WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_keyword' => 'required|string|max:255',
            'trigger_match_type' => 'required|in:exact,contains,starts_with',
            'is_active' => 'boolean',
        ]);

        $flow->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trigger_keyword' => $validated['trigger_keyword'],
            'trigger_match_type' => $validated['trigger_match_type'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.success.flow_updated'));
    }

    public function destroy(WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);
        $flow->delete();

        return back()->with('success', __('messages.success.flow_deleted'));
    }

    public function nodes(WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);

        $flow->load('nodes');
        $aiKeys = WaAiKey::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('flows.nodes', compact('flow', 'aiKeys'));
    }

    public function nodesStore(Request $request, WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'nodes' => 'required|array',
            'nodes.*.id' => 'nullable|integer|exists:wa_flow_nodes,id',
            'nodes.*.type' => 'required|string|in:message,condition,ai,wait',
            'nodes.*.label' => 'required|string|max:255',
            'nodes.*.position_x' => 'nullable|integer',
            'nodes.*.position_y' => 'nullable|integer',
            'nodes.*.config' => 'nullable|array',
            'nodes.*.reply_message' => 'nullable|string|max:5000',
            'nodes.*.media_url' => 'nullable|url|max:1000',
            'nodes.*.ai_key_id' => 'nullable|exists:wa_ai_keys,id',
            'nodes.*.condition_field' => 'nullable|string|max:255',
            'nodes.*.condition_operator' => 'nullable|string|max:50',
            'nodes.*.condition_value' => 'nullable|string|max:500',
            'nodes.*.next_node_id_true' => 'nullable|integer',
            'nodes.*.next_node_id_false' => 'nullable|integer',
            'nodes.*.wait_seconds' => 'nullable|integer|min:1',
            'nodes.*.sort_order' => 'nullable|integer',
        ]);

        $existingIds = $flow->nodes()->pluck('id')->toArray();
        $submittedIds = collect($validated['nodes'])->pluck('id')->filter()->toArray();
        $toDelete = array_diff($existingIds, $submittedIds);

        if (!empty($toDelete)) {
            WaFlowNode::whereIn('id', $toDelete)->delete();
        }

        $idMap = [];

        foreach ($validated['nodes'] as $index => $data) {
            $nodeId = $data['id'] ?? null;

            $nodeData = [
                'flow_id' => $flow->id,
                'type' => $data['type'],
                'label' => $data['label'],
                'position_x' => $data['position_x'] ?? 0,
                'position_y' => $data['position_y'] ?? 0,
                'config' => $data['config'] ?? null,
                'reply_message' => $data['reply_message'] ?? null,
                'media_url' => $data['media_url'] ?? null,
                'ai_key_id' => $data['ai_key_id'] ?? null,
                'condition_field' => $data['condition_field'] ?? null,
                'condition_operator' => $data['condition_operator'] ?? null,
                'condition_value' => $data['condition_value'] ?? null,
                'next_node_id_true' => null,
                'next_node_id_false' => null,
                'wait_seconds' => $data['wait_seconds'] ?? null,
                'sort_order' => $data['sort_order'] ?? $index,
            ];

            if ($nodeId) {
                $node = WaFlowNode::where('id', $nodeId)->where('flow_id', $flow->id)->first();
                if ($node) {
                    $node->update($nodeData);
                }
            } else {
                $node = WaFlowNode::create($nodeData);
                $nodeId = $node->id;
            }

            $idMap[$index] = $nodeId;
        }

        foreach ($validated['nodes'] as $index => $data) {
            $trueIdx = $data['next_node_id_true'] ?? null;
            $falseIdx = $data['next_node_id_false'] ?? null;

            WaFlowNode::where('id', $idMap[$index])->update([
                'next_node_id_true' => is_int($trueIdx) && isset($idMap[$trueIdx]) ? $idMap[$trueIdx] : null,
                'next_node_id_false' => is_int($falseIdx) && isset($idMap[$falseIdx]) ? $idMap[$falseIdx] : null,
            ]);
        }

        return back()->with('success', __('messages.success.flow_node_saved'));
    }

    public function aiGenerate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'ai_key_id' => 'required|exists:wa_ai_keys,id',
        ]);

        $aiKey = WaAiKey::where('user_id', Auth::id())->findOrFail($request->ai_key_id);

        $systemPrompt = "You are a WhatsApp chatbot flow builder. Generate a complete flow as JSON based on the user's description.

Return ONLY valid JSON in this exact format:
{
  \"name\": \"Flow name based on description\",
  \"nodes\": [
    {
      \"type\": \"trigger\",
      \"label\": \"Start\",
      \"sort_order\": 1
    },
    {
      \"type\": \"message\",
      \"label\": \"Node label\",
      \"reply_message\": \"Reply text\",
      \"sort_order\": 2,
      \"next_node_id_true\": null
    },
    {
      \"type\": \"condition\",
      \"label\": \"Check something\",
      \"condition_field\": \"text\",
      \"condition_operator\": \"contains\",
      \"condition_value\": \"keyword\",
      \"sort_order\": 3,
      \"next_node_id_true\": null,
      \"next_node_id_false\": null
    },
    {
      \"type\": \"ai\",
      \"label\": \"AI Response\",
      \"sort_order\": 4
    }
  ]
}

Available node types: trigger, message, image, button, ai, condition, wait
Condition operators: equals, contains, not_contains
Make 3-8 nodes maximum. " . __('ai.flow_generation_language_hint');

        $aiService = app(\App\Services\AiService::class);
        $response = $aiService->send($aiKey, $systemPrompt . "\n\nUser request: " . $request->prompt);

        $flowData = json_decode($response, true);

        if (!$flowData || empty($flowData['nodes'])) {
            return back()->with('error', __('messages.error.ai_flow_generation_failed'));
        }

        $flow = WaFlow::create([
            'user_id' => Auth::id(),
            'name' => $flowData['name'] ?? 'AI Generated Flow',
            'is_active' => false,
        ]);

        $nodeIds = [];
        foreach ($flowData['nodes'] as $i => $nodeData) {
            $node = WaFlowNode::create([
                'flow_id' => $flow->id,
                'type' => $nodeData['type'] ?? 'message',
                'label' => $nodeData['label'] ?? "Node " . ($i + 1),
                'reply_message' => $nodeData['reply_message'] ?? null,
                'condition_field' => $nodeData['condition_field'] ?? null,
                'condition_operator' => $nodeData['condition_operator'] ?? null,
                'condition_value' => $nodeData['condition_value'] ?? null,
                'sort_order' => $i + 1,
            ]);
            $nodeIds[] = $node->id;
        }

        foreach ($flowData['nodes'] as $i => $nodeData) {
            if (isset($nodeData['next_node_id_true']) && is_int($nodeData['next_node_id_true']) && isset($nodeIds[$nodeData['next_node_id_true']])) {
                WaFlowNode::where('id', $nodeIds[$i])->update(['next_node_id_true' => $nodeIds[$nodeData['next_node_id_true']]]);
            }
            if (isset($nodeData['next_node_id_false']) && is_int($nodeData['next_node_id_false']) && isset($nodeIds[$nodeData['next_node_id_false']])) {
                WaFlowNode::where('id', $nodeIds[$i])->update(['next_node_id_false' => $nodeIds[$nodeData['next_node_id_false']]]);
            }
        }

        return redirect()->route('flows.nodes', $flow)
            ->with('success', __('messages.success.flow_ai_created'));
    }
}
