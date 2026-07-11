<?php

namespace App\Services;

use App\Models\WaAiImageJob;
use App\Models\WaAiKey;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AiImageService
{
    /**
     * Generate an image using the AI provider's image endpoint.
     * Falls back to text-to-image via chat completions for providers
     * that only return URLs via vision-capable models.
     */
    public function generate(string $prompt, string $size = 'square', string $style = 'photorealistic', ?WaAiKey $aiKey = null): ?array
    {
        $aiKey = $aiKey ?? WaAiKey::where('user_id', auth()->id())->where('is_active', true)->first();
        if (!$aiKey) return null;

        $fullPrompt = $this->buildPrompt($prompt, $style);
        $dimensions = $this->dimensions($size);

        try {
            $baseUrl = $aiKey->base_url ?: $this->defaultImageUrl($aiKey->provider, $dimensions);
            if (!$baseUrl) return null;

            $payload = $this->buildPayload($aiKey->provider, $fullPrompt, $dimensions, $aiKey->model);

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $aiKey->api_key_encrypted,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post($baseUrl, $payload);

            if ($response->failed()) {
                Log::warning("AiImageService: request failed", ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            return $this->parseImageResponse($aiKey->provider, $data);
        } catch (\Throwable $e) {
            Log::error("AiImageService: exception {$e->getMessage()}");
            return null;
        }
    }

    public function generateMultiple(string $prompt, int $count, string $size = 'square', string $style = 'photorealistic'): array
    {
        $results = [];
        for ($i = 0; $i < $count; $i++) {
            $result = $this->generate($prompt, $size, $style);
            if ($result) {
                $results[] = $result;
            }
        }
        return $results;
    }

    public function generateAndStore(WaAiImageJob $job): void
    {
        $job->update(['status' => 'processing']);

        try {
            $items = [];
            for ($i = 0; $i < $job->count; $i++) {
                $result = $this->generate($job->prompt, $job->size, $job->style);
                if ($result) {
                    $localUrl = $this->downloadAndStore($result['url'] ?? null);
                    if ($localUrl) {
                        $items[] = ['url' => $localUrl, 'prompt' => $job->prompt, 'style' => $job->style];
                    }
                }
            }

            if (empty($items)) {
                $job->update(['status' => 'failed']);
                return;
            }

            $job->update([
                'status' => 'completed',
                'results' => $items,
            ]);
        } catch (\Throwable $e) {
            Log::error("AiImageService::generateAndStore: {$e->getMessage()}");
            $job->update(['status' => 'failed']);
        }
    }

    protected function buildPrompt(string $prompt, string $style): string
    {
        $styleModifiers = [
            'photorealistic' => 'photorealistic, highly detailed, 8K, professional photography',
            'illustration' => 'digital illustration, vector style, clean lines, vibrant colors',
            'anime' => 'anime style, manga art, cel-shaded, vibrant',
            '3d' => '3D render, octane render, cinematic lighting, realistic materials',
            'logo' => 'minimalist logo design, vector, clean, modern, professional',
        ];

        $modifier = $styleModifiers[$style] ?? '';
        return trim("{$prompt}. {$modifier}");
    }

    protected function dimensions(string $size): array
    {
        return match ($size) {
            'landscape' => ['width' => 1792, 'height' => 1024],
            'portrait' => ['width' => 1024, 'height' => 1792],
            default => ['width' => 1024, 'height' => 1024],
        };
    }

    protected function defaultImageUrl(string $provider, array $dimensions): ?string
    {
        return match (strtolower($provider)) {
            'openai' => 'https://api.openai.com/v1/images/generations',
            'deepseek' => null,
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp-image-generation:generateContent',
            default => null,
        };
    }

    protected function buildPayload(string $provider, string $prompt, array $dimensions, string $model): array
    {
        if (strtolower($provider) === 'openai') {
            return [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'n' => 1,
                'size' => "{$dimensions['width']}x{$dimensions['height']}",
                'response_format' => 'url',
            ];
        }

        return [
            'model' => $model,
            'prompt' => $prompt,
            'n' => 1,
            'size' => "{$dimensions['width']}x{$dimensions['height']}",
        ];
    }

    protected function parseImageResponse(string $provider, array $data): ?array
    {
        $url = $data['data'][0]['url'] ?? $data['data'][0]['b64_json'] ?? null;
        if (!$url) return null;

        return ['url' => $url, 'provider' => $provider];
    }

    protected function downloadAndStore(?string $url): ?string
    {
        if (!$url) return null;

        try {
            if (Str::startsWith($url, 'data:')) {
                $response = ['body' => base64_decode(explode(',', $url)[1])];
                $ext = 'png';
            } else {
                $response = \Illuminate\Support\Facades\Http::timeout(60)->get($url);
                if (!$response->successful()) return null;
                $ext = 'png';
            }

            $filename = 'ai-images/' . date('Y/m') . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($filename, $response['body']);
            return Storage::disk('public')->url($filename);
        } catch (\Throwable $e) {
            Log::error("AiImageService::downloadAndStore: {$e->getMessage()}");
            return null;
        }
    }
}
