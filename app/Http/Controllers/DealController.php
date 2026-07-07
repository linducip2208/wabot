<?php

namespace App\Http\Controllers;

use App\Models\WaDeal;
use App\Models\WaDealStage;
use App\Models\WaContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    public function index()
    {
        $deals = WaDeal::where('user_id', Auth::id())
            ->with(['contact', 'stage'])
            ->latest()
            ->get();

        $stages = WaDealStage::where('user_id', Auth::id())
            ->orderBy('sort_order')
            ->get();

        return view('deals.index', compact('deals', 'stages'));
    }

    public function board()
    {
        $stages = WaDealStage::where('user_id', Auth::id())
            ->with(['deals' => function ($query) {
                $query->where('user_id', Auth::id())->with('contact');
            }])
            ->orderBy('sort_order')
            ->get();

        $deals = WaDeal::where('user_id', Auth::id())
            ->with(['contact', 'stage'])
            ->get();

        return view('deals.board', compact('stages', 'deals'));
    }

    public function create()
    {
        $contacts = WaContact::where('user_id', Auth::id())->get();
        $stages = WaDealStage::where('user_id', Auth::id())->orderBy('sort_order')->get();

        return view('deals.create', compact('contacts', 'stages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'required|exists:wa_contacts,id',
            'stage_id' => 'required|exists:wa_deal_stages,id',
            'value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        WaDeal::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'contact_id' => $validated['contact_id'],
            'stage_id' => $validated['stage_id'],
            'value' => $validated['value'] ?? 0,
            'expected_close_date' => $validated['expected_close_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'open',
        ]);

        return redirect()->route('deals.index')->with('success', 'Deal berhasil dibuat.');
    }

    public function edit(WaDeal $deal)
    {
        abort_if($deal->user_id !== Auth::id(), 403);

        $contacts = WaContact::where('user_id', Auth::id())->get();
        $stages = WaDealStage::where('user_id', Auth::id())->orderBy('sort_order')->get();

        return view('deals.edit', compact('deal', 'contacts', 'stages'));
    }

    public function update(Request $request, WaDeal $deal)
    {
        abort_if($deal->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'contact_id' => 'required|exists:wa_contacts,id',
            'stage_id' => 'required|exists:wa_deal_stages,id',
            'value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        $deal->update([
            'title' => $validated['title'],
            'contact_id' => $validated['contact_id'],
            'stage_id' => $validated['stage_id'],
            'value' => $validated['value'] ?? 0,
            'expected_close_date' => $validated['expected_close_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('deals.index')->with('success', 'Deal diperbarui.');
    }

    public function destroy(WaDeal $deal)
    {
        abort_if($deal->user_id !== Auth::id(), 403);
        $deal->delete();

        return back()->with('success', 'Deal dihapus.');
    }

    public function move(Request $request, WaDeal $deal)
    {
        abort_if($deal->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'stage_id' => 'required|exists:wa_deal_stages,id',
        ]);

        $stage = WaDealStage::where('user_id', Auth::id())
            ->where('id', $validated['stage_id'])
            ->first();

        abort_if(!$stage, 404, 'Stage tidak ditemukan.');

        $deal->update(['stage_id' => $validated['stage_id']]);

        return back()->with('success', 'Deal dipindahkan.');
    }
}
