<?php

namespace App\Http\Controllers;

use App\Models\WaCatalog;
use App\Models\WaCatalogItem;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatalogController extends Controller
{
    public function index()
    {
        $catalogs = WaCatalog::where('user_id', Auth::id())
            ->withCount('items')
            ->with('session')
            ->latest()
            ->get();

        return view('catalogs.index', compact('catalogs'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('catalogs.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'session_id' => 'required|exists:wa_sessions,id',
        ]);

        WaCatalog::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'session_id' => $validated['session_id'],
            'is_active' => true,
        ]);

        return redirect()->route('catalogs.index')->with('success', __('messages.success.catalog_created'));
    }

    public function edit(WaCatalog $catalog)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('catalogs.edit', compact('catalog', 'sessions'));
    }

    public function update(Request $request, WaCatalog $catalog)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'session_id' => 'required|exists:wa_sessions,id',
        ]);

        $catalog->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'session_id' => $validated['session_id'],
        ]);

        return redirect()->route('catalogs.index')->with('success', __('messages.success.catalog_updated'));
    }

    public function destroy(WaCatalog $catalog)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);
        $catalog->delete();

        return back()->with('success', __('messages.success.catalog_deleted'));
    }

    public function items(WaCatalog $catalog)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);

        $catalog->load(['items' => function ($query) {
            $query->orderBy('sort_order');
        }]);

        return view('catalogs.items', compact('catalog'));
    }

    public function itemsStore(Request $request, WaCatalog $catalog)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url|max:1000',
            'product_code' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
        ]);

        $maxOrder = WaCatalogItem::where('catalog_id', $catalog->id)->max('sort_order') ?? 0;

        WaCatalogItem::create([
            'catalog_id' => $catalog->id,
            'name' => $validated['name'],
            'price' => $validated['price'],
            'image_url' => $validated['image_url'] ?? null,
            'product_code' => $validated['product_code'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'is_active' => true,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', __('messages.success.catalog_item_added'));
    }

    public function itemsUpdate(Request $request, WaCatalog $catalog, WaCatalogItem $item)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);
        abort_if($item->catalog_id !== $catalog->id, 404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|url|max:1000',
            'product_code' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $item->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'image_url' => $validated['image_url'] ?? null,
            'product_code' => $validated['product_code'] ?? null,
            'stock' => $validated['stock'] ?? 0,
            'sort_order' => $validated['sort_order'] ?? $item->sort_order,
        ]);

        return back()->with('success', __('messages.success.catalog_item_updated'));
    }

    public function itemsDestroy(WaCatalog $catalog, WaCatalogItem $item)
    {
        abort_if($catalog->user_id !== Auth::id(), 403);
        abort_if($item->catalog_id !== $catalog->id, 404);

        $item->delete();

        return back()->with('success', __('messages.success.catalog_item_deleted'));
    }
}
