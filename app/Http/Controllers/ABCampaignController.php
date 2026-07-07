<?php

namespace App\Http\Controllers;

use App\Models\WaCampaignAB;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ABCampaignController extends Controller
{
    public function index()
    {
        $tests = WaCampaignAB::where('user_id', Auth::id())
            ->with('session')
            ->latest()
            ->get();

        return view('ab-tests.index', compact('tests'));
    }

    public function create()
    {
        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('ab-tests.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:wa_sessions,id',
            'variant_a_message' => 'required|string|max:5000',
            'variant_b_message' => 'required|string|max:5000',
            'media_url_a' => 'nullable|url|max:1000',
            'media_url_b' => 'nullable|url|max:1000',
        ]);

        WaCampaignAB::create([
            'user_id' => Auth::id(),
            'session_id' => $validated['session_id'],
            'name' => $validated['name'],
            'variant_a_message' => $validated['variant_a_message'],
            'variant_b_message' => $validated['variant_b_message'],
            'media_url_a' => $validated['media_url_a'] ?? null,
            'media_url_b' => $validated['media_url_b'] ?? null,
            'a_sent' => 0,
            'a_replied' => 0,
            'b_sent' => 0,
            'b_replied' => 0,
            'winner' => null,
            'is_active' => false,
        ]);

        return redirect()->route('ab-tests.index')->with('success', 'A/B test berhasil dibuat.');
    }

    public function edit(WaCampaignAB $test)
    {
        abort_if($test->user_id !== Auth::id(), 403);

        $sessions = WaSession::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('ab-tests.edit', compact('test', 'sessions'));
    }

    public function update(Request $request, WaCampaignAB $test)
    {
        abort_if($test->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'required|exists:wa_sessions,id',
            'variant_a_message' => 'required|string|max:5000',
            'variant_b_message' => 'required|string|max:5000',
            'media_url_a' => 'nullable|url|max:1000',
            'media_url_b' => 'nullable|url|max:1000',
        ]);

        $test->update([
            'name' => $validated['name'],
            'session_id' => $validated['session_id'],
            'variant_a_message' => $validated['variant_a_message'],
            'variant_b_message' => $validated['variant_b_message'],
            'media_url_a' => $validated['media_url_a'] ?? null,
            'media_url_b' => $validated['media_url_b'] ?? null,
        ]);

        return back()->with('success', 'A/B test diperbarui.');
    }

    public function destroy(WaCampaignAB $test)
    {
        abort_if($test->user_id !== Auth::id(), 403);
        $test->delete();

        return back()->with('success', 'A/B test dihapus.');
    }

    public function start(WaCampaignAB $test)
    {
        abort_if($test->user_id !== Auth::id(), 403);

        $test->update([
            'is_active' => true,
            'started_at' => now(),
            'a_sent' => 0,
            'a_replied' => 0,
            'b_sent' => 0,
            'b_replied' => 0,
            'winner' => null,
            'ended_at' => null,
        ]);

        return back()->with('success', 'A/B test dimulai.');
    }

    public function end(WaCampaignAB $test)
    {
        abort_if($test->user_id !== Auth::id(), 403);

        $aRatio = $test->a_sent > 0 ? ($test->a_replied / $test->a_sent) : 0;
        $bRatio = $test->b_sent > 0 ? ($test->b_replied / $test->b_sent) : 0;

        $winner = null;
        if ($aRatio > $bRatio) {
            $winner = 'A';
        } elseif ($bRatio > $aRatio) {
            $winner = 'B';
        } elseif ($aRatio > 0 && $bRatio > 0 && $aRatio === $bRatio) {
            $winner = 'draw';
        }

        $test->update([
            'is_active' => false,
            'ended_at' => now(),
            'winner' => $winner,
        ]);

        return back()->with('success', 'A/B test diakhiri. Pemenang: ' . ($winner ? "Varian {$winner}" : 'Tidak ada pemenang'));
    }
}
