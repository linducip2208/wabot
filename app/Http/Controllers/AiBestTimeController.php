<?php

namespace App\Http\Controllers;

use App\Models\WaAiKey;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiBestTimeController extends Controller
{
    public function index()
    {
        $platforms = ['whatsapp', 'instagram', 'facebook', 'twitter', 'telegram', 'email'];

        return view('ai-best-time.index', compact('platforms'));
    }

    public function suggest(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:whatsapp,instagram,facebook,twitter,telegram,email',
            'niche' => 'nullable|string|max:500',
            'target_audience' => 'nullable|string|max:500',
            'timezone' => 'nullable|string|max:100',
        ]);

        $aiKey = WaAiKey::where('user_id', Auth::id())->where('is_active', true)->first();
        if (!$aiKey) {
            return back()->with('error', __('aistudio.no_active_ai_key'))->withInput();
        }

        $prompt = $this->buildTimePrompt(
            $validated['platform'],
            $validated['niche'] ?? '',
            $validated['target_audience'] ?? '',
            $validated['timezone'] ?? 'WIB (UTC+7)'
        );

        $aiService = app(AiService::class);
        try {
            $result = $aiService->rawPrompt($aiKey, $prompt);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($result === null) {
            return back()->with('error', __('messages.error.ai_response_failed'))->withInput();
        }

        $recommendations = $this->parseRecommendations($result);

        return back()->with('success', __('aibesttime.recommendations_ready'))
            ->with('recommendations', $recommendations)
            ->with('selected_platform', $validated['platform'])
            ->withInput();
    }

    protected function buildTimePrompt(string $platform, string $niche, string $audience, string $timezone): string
    {
        $nicheText = $niche ? "Niche bisnis: {$niche}." : '';
        $audienceText = $audience ? "Target audience: {$audience}." : '';

        return <<<PROMPT
Kamu adalah social media strategist yang ahli dalam optimalisasi waktu posting.

Platform: {$platform}
{$nicheText}
{$audienceText}
Timezone: {$timezone}

Berikan rekomendasi waktu posting terbaik untuk platform {$platform} dalam format JSON:

{
  "platform": "{$platform}",
  "timezone": "{$timezone}",
  "best_days": ["Senin", "Rabu", "Jumat"],
  "schedule": {
    "Senin": [
      {"time": "08:00", "score": 85, "reason": "Jam mulai kerja"},
      {"time": "12:00", "score": 75, "reason": "Jam istirahat"}
    ],
    "Selasa": [...],
    ...
  },
  "tips": ["Gunakan hashtag trending", "Posting konsisten", ...]
}

Score 0-100 menunjukkan confidence level. Berikan minimal 2 slot per hari.
Tulis semua teks dalam Bahasa Indonesia.
Jangan sertakan teks lain selain JSON valid.
PROMPT;
    }

    protected function parseRecommendations(string $rawResult): array
    {
        if (preg_match('/\{[\s\S]*\}/', $rawResult, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        $decoded = json_decode(trim($rawResult), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return ['raw' => $rawResult, 'platform' => '', 'schedule' => [], 'tips' => []];
    }
}
