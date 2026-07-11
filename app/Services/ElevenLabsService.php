<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class ElevenLabsService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client(['timeout' => 60, 'http_errors' => false]);
    }

    public function textToSpeech(string $apiKey, string $text, string $voiceId = '21m00Tcm4TlvDq8ikWAM', string $modelId = 'eleven_multilingual_v2'): ?string
    {
        try {
            $res = $this->http->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'headers' => [
                    'xi-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'audio/mpeg',
                ],
                'json' => [
                    'text' => $text,
                    'model_id' => $modelId,
                    'voice_settings' => [
                        'stability' => 0.5,
                        'similarity_boost' => 0.75,
                    ],
                ],
            ]);

            if ($res->getStatusCode() === 200) {
                return $res->getBody()->getContents();
            }

            Log::error('ElevenLabs TTS failed', ['status' => $res->getStatusCode(), 'body' => $res->getBody()->getContents()]);
            return null;
        } catch (GuzzleException $e) {
            Log::error("ElevenLabsService error: {$e->getMessage()}");
            return null;
        }
    }

    public function getVoices(string $apiKey): array
    {
        try {
            $res = $this->http->get('https://api.elevenlabs.io/v1/voices', [
                'headers' => ['xi-api-key' => $apiKey],
            ]);

            return json_decode($res->getBody()->getContents(), true)['voices'] ?? [];
        } catch (GuzzleException $e) {
            return [];
        }
    }
}
