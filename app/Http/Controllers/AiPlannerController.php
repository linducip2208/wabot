<?php

namespace App\Http\Controllers;

use App\Models\WaAiContentPlan;
use App\Models\WaAiKey;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiPlannerController extends Controller
{
    public function index()
    {
        $plans = WaAiContentPlan::where('user_id', Auth::id())->latest()->get();
        return view('ai-planner.index', compact('plans'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'niche' => 'required|string|max:500',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'string|in:whatsapp,instagram,facebook,twitter,telegram,email',
            'frequency' => 'required|string|in:weekly,daily,monthly',
            'duration' => 'required|integer|min:1|max:12',
            'name' => 'required|string|max:255',
        ]);

        $aiKey = WaAiKey::where('user_id', Auth::id())->where('is_active', true)->first();
        if (!$aiKey) {
            return back()->with('error', __('aistudio.no_active_ai_key'));
        }

        $platforms = implode(', ', $validated['platforms']);
        $frequency = $validated['frequency'];
        $duration = $validated['duration'];

        $prompt = $this->buildPlannerPrompt($validated['niche'], $platforms, $frequency, $duration);

        $aiService = app(AiService::class);
        try {
            $result = $aiService->rawPrompt($aiKey, $prompt);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($result === null) {
            return back()->with('error', __('messages.error.ai_response_failed'))->withInput();
        }

        $calendarData = $this->parseCalendarData($result, $validated['platforms'], $frequency, $duration);

        $plan = WaAiContentPlan::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'platforms' => $validated['platforms'],
            'frequency' => $frequency,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeeks($duration)->toDateString(),
            'status' => 'generated',
            'calendar_data' => $calendarData,
        ]);

        return redirect()->route('ai-planner.index')->with('success', __('aiplanner.plan_generated'))
            ->with('view_plan_id', $plan->id);
    }

    public function destroy(WaAiContentPlan $plan)
    {
        abort_if($plan->user_id !== Auth::id(), 403);
        $plan->delete();
        return back()->with('success', __('aiplanner.plan_deleted'));
    }

    protected function buildPlannerPrompt(string $niche, string $platforms, string $frequency, string $duration): string
    {
        return <<<PROMPT
Kamu adalah social media content planner profesional untuk bisnis.

Bisnis/niche: {$niche}
Platform: {$platforms}
Frekuensi posting: {$frequency}
Durasi: {$duration} minggu

Buatkan content calendar dalam bahasa Indonesia selama {$duration} minggu ke depan.
Untuk setiap minggu, berikan:
1. Tema mingguan
2. Untuk setiap platform, berikan 1-3 topik konten spesifik per minggu (sesuai frekuensi {$frequency})

Format output HARUS JSON valid seperti ini:
{
  "weeks": [
    {
      "week": 1,
      "theme": "Tema minggu ini",
      "platforms": {
        "whatsapp": ["Topik 1", "Topik 2"],
        "instagram": ["Topik 1", "Topik 2", "Topik 3"]
      }
    }
  ]
}

Jangan sertakan teks lain selain JSON di atas.
PROMPT;
    }

    protected function parseCalendarData(string $rawResult, array $platforms, string $frequency, int $duration): array
    {
        $json = $this->extractJson($rawResult);
        if ($json) {
            return $json;
        }

        $lines = array_filter(explode("\n", $rawResult));
        $weeks = [];
        $currentWeek = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/minggu\s*(\d+)/i', $line, $m)) {
                if ($currentWeek) {
                    $weeks[] = $currentWeek;
                }
                $currentWeek = ['week' => (int)$m[1], 'theme' => $line, 'platforms' => []];
            } elseif ($currentWeek && preg_match('/^[-*]\s*(.+)/', $line, $m)) {
                $currentWeek['platforms']['general'][] = $m[1];
            }
        }
        if ($currentWeek) {
            $weeks[] = $currentWeek;
        }

        return $weeks ? ['weeks' => $weeks] : ['raw' => $rawResult, 'weeks' => []];
    }

    protected function extractJson(string $text): ?array
    {
        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        $text = trim($text);
        if (str_starts_with($text, '{')) {
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return null;
    }
}
