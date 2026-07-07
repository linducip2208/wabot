<?php

namespace App\Http\Controllers;

use App\Models\WaSentimentLog;
use App\Services\SentimentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SentimentController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'today');
        if (!in_array($period, ['today', 'week', 'month'])) {
            $period = 'today';
        }

        $service = app(SentimentService::class);

        $statsToday = $service->getStats(Auth::id(), 'today');
        $statsWeek = $service->getStats(Auth::id(), 'week');
        $statsMonth = $service->getStats(Auth::id(), 'month');

        $recentLogs = WaSentimentLog::where('user_id', Auth::id())
            ->with(['contact', 'message'])
            ->latest()
            ->limit(50)
            ->get();

        $distribution = WaSentimentLog::where('user_id', Auth::id())
            ->selectRaw('sentiment, COUNT(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment')
            ->toArray();

        $chartData = [
            'labels' => ['Positif', 'Netral', 'Negatif'],
            'values' => [
                $distribution['positive'] ?? 0,
                $distribution['neutral'] ?? 0,
                $distribution['negative'] ?? 0,
            ],
        ];

        $dailyData = WaSentimentLog::where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subDays(14))
            ->selectRaw("DATE(created_at) as date, sentiment, COUNT(*) as count")
            ->groupBy('date', 'sentiment')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        $trendLabels = [];
        $trendPositive = [];
        $trendNeutral = [];
        $trendNegative = [];
        foreach ($dailyData as $date => $entries) {
            $trendLabels[] = $date;
            $dayData = $entries->pluck('count', 'sentiment')->toArray();
            $trendPositive[] = $dayData['positive'] ?? 0;
            $trendNeutral[] = $dayData['neutral'] ?? 0;
            $trendNegative[] = $dayData['negative'] ?? 0;
        }

        $trendChart = [
            'labels' => $trendLabels,
            'positive' => $trendPositive,
            'neutral' => $trendNeutral,
            'negative' => $trendNegative,
        ];

        return view('sentiment.index', compact(
            'statsToday',
            'statsWeek',
            'statsMonth',
            'recentLogs',
            'chartData',
            'trendChart',
            'period'
        ));
    }
}
