<?php

namespace App\Http\Controllers;

use App\Models\WaDealStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealStageController extends Controller
{
    public function index()
    {
        $stages = WaDealStage::where('user_id', Auth::id())
            ->withCount('deals')
            ->orderBy('sort_order')
            ->get();

        return view('deals.stages', compact('stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $maxOrder = WaDealStage::where('user_id', Auth::id())->max('sort_order') ?? 0;

        WaDealStage::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#6366f1',
            'sort_order' => $validated['sort_order'] ?? $maxOrder + 1,
        ]);

        return back()->with('success', 'Stage baru ditambahkan.');
    }

    public function update(Request $request, WaDealStage $stage)
    {
        abort_if($stage->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $stage->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? $stage->color,
            'sort_order' => $validated['sort_order'] ?? $stage->sort_order,
        ]);

        return back()->with('success', 'Stage diperbarui.');
    }

    public function destroy(WaDealStage $stage)
    {
        abort_if($stage->user_id !== Auth::id(), 403);

        if ($stage->deals()->count() > 0) {
            return back()->with('error', 'Stage tidak dapat dihapus karena masih memiliki deal.');
        }

        $stage->delete();

        return back()->with('success', 'Stage dihapus.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:wa_deal_stages,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            WaDealStage::where('user_id', Auth::id())
                ->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return back()->with('success', 'Urutan stage diperbarui.');
    }
}
