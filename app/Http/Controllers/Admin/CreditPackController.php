<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaCreditPack;
use App\Services\CreditService;
use Illuminate\Http\Request;

class CreditPackController extends Controller
{
    public function index()
    {
        $packs = WaCreditPack::orderBy('sort_order')->get();
        return view('admin.credit-packs.index', compact('packs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        WaCreditPack::create([
            'name' => $data['name'],
            'credits' => $data['credits'],
            'price' => $data['price'],
            'is_active' => true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', __('admin.credit_pack_created'));
    }

    public function toggle(WaCreditPack $pack)
    {
        $pack->update(['is_active' => !$pack->is_active]);
        return back()->with('success', __('admin.credit_pack_toggled'));
    }

    public function destroy(WaCreditPack $pack)
    {
        $pack->delete();
        return back()->with('success', __('admin.credit_pack_deleted'));
    }
}
