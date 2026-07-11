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
     * Gemini & DeepSeek juga mendukung format yg sama.
     */
    public function send(WaAiKey $aiKey, string $userMessage, ?array $knowledgeRows = null): ?string
    {
        $baseUrl = $aiKey->base_url ?: $this->defaultBaseUrl($aiKey->provider);
        if (!$baseUrl) {
            Log::error("AiService: unknown provider or base_url", ['provider' => $aiKey->provider]);
            return null;
        }

        $systemPrompt = $aiKey->system_prompt
            ?: __('ai.default_system_prompt');

        $systemPrompt .= "\n\n" . $this->guardrails();

        if ($knowledgeRows && count($knowledgeRows) > 0) {
            $kbText = json_encode(array_slice($knowledgeRows, 0, 15), JSON_UNESCAPED_UNICODE);
            $systemPrompt .= "\n\n" . __('ai.knowledge_base_prefix') . "\n" . $kbText;
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
     * Guardrails bisnis wajib — selalu di-append ke setiap system prompt.
     * Tidak bisa di-bypass oleh user (meskipun pakai custom system_prompt).
     */
    protected function guardrails(): string
    {
        return __('ai.guardrails.header') . "\n\n"
            . "1. " . __('ai.guardrails.rule_1') . "\n\n"
            . "2. " . __('ai.guardrails.rule_2') . "\n\n"
            . "3. " . __('ai.guardrails.rule_3') . "\n\n"
            . "4. " . __('ai.guardrails.rule_4') . "\n\n"
            . "5. " . __('ai.guardrails.rule_5') . "\n\n"
            . "6. " . __('ai.guardrails.rule_6') . "\n\n"
            . "7. " . __('ai.guardrails.rule_7');
    }

    /**
     * Kirim prompt mentah ke AI tanpa guardrails & knowledge base.
     * Dipakai untuk analisis internal (sentiment, intent, dll).
     */
    public function rawPrompt(WaAiKey $aiKey, string $prompt): ?string
    {
        $baseUrl = $aiKey->base_url ?: $this->defaultBaseUrl($aiKey->provider);
        if (!$baseUrl) return null;

        $messages = [
            ['role' => 'user', 'content' => $prompt],
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $aiKey->api_key_encrypted,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($baseUrl, [
                'model' => $aiKey->model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 300,
            ]);

            if ($response->failed()) return null;
            $data = $response->json();
            return $data['choices'][0]['message']['content'] ?? null;
        } catch (\Throwable $e) {
            Log::error("AiService::rawPrompt exception: {$e->getMessage()}");
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
