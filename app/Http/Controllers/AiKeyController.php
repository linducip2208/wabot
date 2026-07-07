<?php

namespace App\Http\Controllers;

use App\Models\WaAiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class AiKeyController extends Controller
{
    public function index()
    {
        $keys = WaAiKey::where('user_id', Auth::id())->latest()->get();
        return view('ai-keys.index', compact('keys'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'provider'      => 'required|in:openai,gemini,anthropic,openai_compatible',
            'model'         => 'required|string|max:255',
            'base_url'      => 'nullable|url|max:500',
            'api_key'       => 'required|string|max:500',
            'system_prompt' => 'nullable|string|max:2000',
            'max_tokens'    => 'nullable|integer|min:1|max:128000',
            'temperature'   => 'nullable|numeric|min:0|max:2',
        ]);

        WaAiKey::create([
            'user_id'           => Auth::id(),
            'name'              => $data['name'],
            'provider'          => $data['provider'],
            'model'             => $data['model'],
            'base_url'          => $data['base_url'] ?? null,
            'api_key_encrypted' => Crypt::encryptString($data['api_key']),
            'system_prompt'     => $data['system_prompt'] ?? null,
            'max_tokens'        => $data['max_tokens'] ?? 1024,
            'temperature'       => $data['temperature'] ?? 0.7,
        ]);

        return back()->with('success', 'AI Key berhasil ditambahkan.');
    }

    public function update(Request $request, WaAiKey $aiKey)
    {
        abort_if($aiKey->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'provider'      => 'required|in:openai,gemini,anthropic,openai_compatible',
            'model'         => 'required|string|max:255',
            'base_url'      => 'nullable|url|max:500',
            'api_key'       => 'nullable|string|max:500',
            'system_prompt' => 'nullable|string|max:2000',
            'max_tokens'    => 'nullable|integer|min:1|max:128000',
            'temperature'   => 'nullable|numeric|min:0|max:2',
        ]);

        $updateData = [
            'name'          => $data['name'],
            'provider'      => $data['provider'],
            'model'         => $data['model'],
            'base_url'      => $data['base_url'] ?? null,
            'system_prompt' => $data['system_prompt'] ?? null,
            'max_tokens'    => $data['max_tokens'] ?? 1024,
            'temperature'   => $data['temperature'] ?? 0.7,
        ];

        if (!empty($data['api_key'])) {
            $updateData['api_key_encrypted'] = Crypt::encryptString($data['api_key']);
        }

        $aiKey->update($updateData);

        return back()->with('success', 'AI Key berhasil diperbarui.');
    }

    public function destroy(WaAiKey $aiKey)
    {
        abort_if($aiKey->user_id !== Auth::id(), 403);
        $aiKey->delete();
        return back()->with('success', 'AI Key dihapus.');
    }

    public function test(WaAiKey $aiKey)
    {
        abort_if($aiKey->user_id !== Auth::id(), 403);

        try {
            $apiKey = Crypt::decryptString($aiKey->api_key_encrypted);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mendekripsi API key.');
        }

        $testPrompt = 'Say hello in one word.';
        $systemPrompt = $aiKey->system_prompt ?: 'You are a helpful assistant. Reply concisely.';

        try {
            switch ($aiKey->provider) {
                case 'openai':
                    $res = Http::withToken($apiKey)
                        ->timeout(30)
                        ->post('https://api.openai.com/v1/chat/completions', [
                            'model'       => $aiKey->model,
                            'messages'    => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user', 'content' => $testPrompt],
                            ],
                            'max_tokens'  => $aiKey->max_tokens ?? 64,
                            'temperature' => $aiKey->temperature ?? 0.7,
                        ]);
                    break;

                case 'gemini':
                    $res = Http::timeout(30)
                        ->post("https://generativelanguage.googleapis.com/v1beta/models/{$aiKey->model}:generateContent?key=" . $apiKey, [
                            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                            'contents'           => [['parts' => [['text' => $testPrompt]]]],
                            'generationConfig'   => [
                                'maxOutputTokens' => $aiKey->max_tokens ?? 64,
                                'temperature'     => $aiKey->temperature ?? 0.7,
                            ],
                        ]);
                    break;

                case 'anthropic':
                    $res = Http::withHeaders([
                        'x-api-key'         => $apiKey,
                        'anthropic-version' => '2023-06-01',
                    ])->timeout(30)
                        ->post('https://api.anthropic.com/v1/messages', [
                            'model'      => $aiKey->model,
                            'system'     => $systemPrompt,
                            'messages'   => [
                                ['role' => 'user', 'content' => $testPrompt],
                            ],
                            'max_tokens' => $aiKey->max_tokens ?? 64,
                        ]);
                    break;

                case 'openai_compatible':
                    $baseUrl = rtrim($aiKey->base_url ?: 'https://api.openai.com/v1', '/');
                    $res = Http::withToken($apiKey)
                        ->timeout(30)
                        ->post("{$baseUrl}/chat/completions", [
                            'model'       => $aiKey->model,
                            'messages'    => [
                                ['role' => 'system', 'content' => $systemPrompt],
                                ['role' => 'user', 'content' => $testPrompt],
                            ],
                            'max_tokens'  => $aiKey->max_tokens ?? 64,
                            'temperature' => $aiKey->temperature ?? 0.7,
                        ]);
                    break;

                default:
                    return back()->with('error', 'Provider tidak dikenal.');
            }

            if ($res->successful()) {
                $body = $res->json();
                $text = '';

                if ($aiKey->provider === 'gemini') {
                    $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '(respons kosong)';
                } elseif ($aiKey->provider === 'anthropic') {
                    $text = $body['content'][0]['text'] ?? '(respons kosong)';
                } else {
                    $text = $body['choices'][0]['message']['content'] ?? '(respons kosong)';
                }

                return back()->with('success', "Test berhasil! Respons: {$text}");
            }

            return back()->with('error', 'HTTP ' . $res->status() . ': ' . ($res->json()['error']['message'] ?? $res->body()));

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal terhubung: ' . $e->getMessage());
        }
    }
}
