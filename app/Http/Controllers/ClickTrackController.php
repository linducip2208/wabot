<?php

namespace App\Http\Controllers;

use App\Models\WaClickEvent;
use App\Services\ClickTrackerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClickTrackController extends Controller
{
    public function __construct(
        protected ClickTrackerService $clickTracker,
    ) {}

    public function redirect(string $token)
    {
        $url = $this->clickTracker->redirect($token);

        if (!$url) {
            abort(404);
        }

        return redirect()->away($url);
    }

    public function index(Request $request)
    {
        $campaignId = $request->get('campaign_id');

        $stats = $this->clickTracker->getStats(Auth::id(), $campaignId);

        $events = WaClickEvent::where('user_id', Auth::id())
            ->when($campaignId, fn($q) => $q->where('campaign_id', $campaignId))
            ->with('campaign')
            ->latest()
            ->paginate(25);

        return view('click-track.index', compact('stats', 'events', 'campaignId'));
    }
}
