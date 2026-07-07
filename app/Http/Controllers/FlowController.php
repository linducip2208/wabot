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

        return redirect()->route('flows.index')->with('success', 'Flow berhasil dibuat.');
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

        return back()->with('success', 'Flow diperbarui.');
    }

    public function destroy(WaFlow $flow)
    {
        abort_if($flow->user_id !== Auth::id(), 403);
        $flow->delete();

        return back()->with('success', 'Flow dihapus.');
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

        return back()->with('success', 'Node flow berhasil disimpan.');
    }
}
