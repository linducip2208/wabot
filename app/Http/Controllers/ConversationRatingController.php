<?php

namespace App\Http\Controllers;

use App\Models\WaConversationRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationRatingController extends Controller
{
    public function index(Request $request)
    {
        $query = WaConversationRating::where('user_id', Auth::id())
            ->with(['contact', 'message']);

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $ratings = $query->latest()->paginate(25)->withQueryString();

        $statsQuery = WaConversationRating::where('user_id', Auth::id());
        $average = round($statsQuery->avg('rating') ?? 0, 1);
        $totalRatings = $statsQuery->count();

        $distribution = WaConversationRating::where('user_id', Auth::id())
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distributionData = [];
        for ($i = 1; $i <= 5; $i++) {
            $distributionData[$i] = $distribution[$i] ?? 0;
        }

        return view('ratings.index', compact(
            'ratings',
            'average',
            'totalRatings',
            'distributionData'
        ));
    }

    public function show(WaConversationRating $rating)
    {
        abort_if($rating->user_id !== Auth::id(), 403);

        $rating->load(['contact', 'message']);

        return view('ratings.show', compact('rating'));
    }

    public function stats()
    {
        $statsQuery = WaConversationRating::where('user_id', Auth::id());

        $average = round($statsQuery->avg('rating') ?? 0, 1);
        $totalRatings = $statsQuery->count();

        $distribution = WaConversationRating::where('user_id', Auth::id())
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $distributionData = [];
        for ($i = 1; $i <= 5; $i++) {
            $distributionData[$i] = $distribution[$i] ?? 0;
        }

        return response()->json([
            'average' => $average,
            'total_ratings' => $totalRatings,
            'distribution' => $distributionData,
        ]);
    }
}
