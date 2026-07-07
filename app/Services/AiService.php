<?php

namespace App\Services;

use App\Models\WaAiKey;
use App\Models\WaKnowledge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Kirim prompt ke AI provider (OpenAI-compatible format).
     * Gemina & DeepSeek juga mendukung format yg sama.
     */
    public function send(WaAiKey $aiKey, string $userMessage, ?array $knowledgeRows = null): ?string
    {
        $baseUrl = $aiKey->base_url ?: $this->defaultBaseUrl($aiKey->provider);
        if (!$baseUrl) {
            Log::error("AiService: unknown provider or base_url", ['provider' => $aiKey->provider]);
            return null;
        }

        $systemPrompt = $aiKey->system_prompt ?: 'Kamu adalah asisten yang membantu. Jawab dengan sopan dan ringkas.';
        if ($knowledgeRows && count($knowledgeRows) > 0) {
            $kbText = json_encode(array_slice($knowledgeRows, 0, 15), JSON_UNESCAPED_UNICODE);
            $systemPrompt .= "\n\nBerikut knowledge base yang bisa kamu gunakan untuk menjawab:\n" . $kbText;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $timeout = min(60, ($aiKey->max_tokens / 10) + 5);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $aiKey->api_key_encrypted,
                'Content-Type' => 'application/json',
            ])->timeout($timeout)->post($baseUrl, [
                'model' => $aiKey->model,
                'messages' => $messages,
                'temperature' => $aiKey->temperature ?? 0.7,
                'max_tokens' => $aiKey->max_tokens ?? 500,
            ]);

            if ($response->failed()) {
                Log::warning("AiService: API request failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Throwable $e) {
            Log::error("AiService: exception {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Kumpulkan semua knowledge aktif milik user dalam bentuk array sederhana.
     */
    public function getKnowledgeContext(int $userId): array
    {
        $all = WaKnowledge::active()->where('user_id', $userId)->get();
        $rows = [];
        foreach ($all as $kb) {
            try {
                $content = json_decode($kb->content, true);
                $kbRows = $content['rows'] ?? [];
                foreach ($kbRows as $r) {
                    $r['_kb'] = $kb->title;
                    $rows[] = $r;
                }
            } catch (\Throwable) {}
        }
        return $rows;
    }

    protected function defaultBaseUrl(string $provider): ?string
    {
        return match (strtolower($provider)) {
            'openai' => 'https://api.openai.com/v1/chat/completions',
            'deepseek' => 'https://api.deepseek.com/v1/chat/completions',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/' . 'gemini-2.0-flash' . ':generateContent',
            default => null,
        };
    }
}
