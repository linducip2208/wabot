<?php

namespace App\Services;

use App\Models\WaAiKey;
use App\Models\WaSentimentLog;
use App\Models\WaContact;
use Illuminate\Support\Facades\Log;

class SentimentService
{
    /**
     * Analisis sentimen pesan masuk pakai AI, dengan fallback keyword-based.
     */
    public function analyze(WaAiKey $aiKey, string $message, int $contactId, int $userId, ?int $msgId = null): array
    {
        $keywordResult = $this->keywordAnalysis($message);
        if ($keywordResult['confidence'] >= 0.85) {
            $this->saveLog($userId, $contactId, $msgId, $message, $keywordResult);
            return $keywordResult;
        }

        $aiResult = $this->aiAnalysis($aiKey, $message);

        if ($aiResult) {
            $this->saveLog($userId, $contactId, $msgId, $message, $aiResult);
            return $aiResult;
        }

        $this->saveLog($userId, $contactId, $msgId, $message, $keywordResult);
        return $keywordResult;
    }

    protected function keywordAnalysis(string $message): array
    {
        $msg = mb_strtolower($message);

        $positive = ['terima kasih', 'makasih', 'thanks', 'bagus', 'mantap', 'oke', 'sip', 'good',
            'suka', 'recommended', 'puas', 'wow', 'top', 'keren', '👍', '❤️', '😊', 'senang',
            'membantu', 'cepat', 'respon', 'good job', 'terbaik', 'hebat'];

        $negative = ['bodoh', 'goblok', 'anjir', 'anjing', 'tolol', 'sial', 'busuk', 'jelek',
            'buruk', 'kecewa', 'marah', 'benci', 'sampah', 'penipu', 'scam', 'tipu',
            'tidak membantu', 'lama', 'lemot', 'slow', 'gak guna', 'tidak berguna',
            'salah', 'error', 'gagal', 'refund', 'komplain', 'complaint', '😡', '🤬', '👎'];

        $posCount = 0;
        $negCount = 0;

        foreach ($positive as $word) {
            if (str_contains($msg, $word)) $posCount++;
        }
        foreach ($negative as $word) {
            if (str_contains($msg, $word)) $negCount++;
        }

        $total = $posCount + $negCount;
        if ($total === 0) {
            return ['sentiment' => 'neutral', 'confidence' => 0.6];
        }

        $ratio = $posCount / $total;
        if ($ratio >= 0.7) return ['sentiment' => 'positive', 'confidence' => $ratio];
        if ($ratio <= 0.3) return ['sentiment' => 'negative', 'confidence' => 1 - $ratio];
        return ['sentiment' => 'neutral', 'confidence' => 0.5];
    }

    protected function aiAnalysis(WaAiKey $aiKey, string $message): ?array
    {
        try {
            $aiService = app(AiService::class);
            $prompt = __('ai.sentiment_analysis_prompt', ['message' => $message]);
            $response = $aiService->rawPrompt($aiKey, $prompt);

            if (!$response) return null;

            $json = json_decode(trim($response), true);
            if ($json && isset($json['sentiment'])) {
                return [
                    'sentiment' => $json['sentiment'],
                    'confidence' => floatval($json['confidence'] ?? 0.5),
                    'raw_response' => $response,
                ];
            }
        } catch (\Throwable $e) {
            Log::warning("SentimentService AI analysis failed: {$e->getMessage()}");
        }
        return null;
    }

    protected function saveLog(int $userId, int $contactId, ?int $msgId, string $message, array $result): void
    {
        $channel = 'unknown';
        $contact = WaContact::find($contactId);
        if ($contact) {
            if (str_starts_with($contact->phone, 'ig:')) $channel = 'instagram';
            elseif (str_starts_with($contact->phone, 'tg:')) $channel = 'telegram';
            else $channel = 'whatsapp';
        }

        WaSentimentLog::create([
            'user_id' => $userId,
            'contact_id' => $contactId,
            'channel' => $channel,
            'message_id' => $msgId,
            'message_text' => mb_substr($message, 0, 500),
            'sentiment' => $result['sentiment'],
            'confidence' => $result['confidence'],
            'raw_response' => $result['raw_response'] ?? null,
        ]);
    }

    public function getStats(int $userId, string $period = 'today'): array
    {
        $query = WaSentimentLog::where('user_id', $userId);
        if ($period === 'today') $query->whereDate('created_at', today());
        elseif ($period === 'week') $query->where('created_at', '>=', now()->subWeek());
        elseif ($period === 'month') $query->where('created_at', '>=', now()->subMonth());

        $total = $query->count();
        return [
            'total' => $total,
            'positive' => $total > 0 ? round(($query->clone()->where('sentiment', 'positive')->count() / $total) * 100, 1) : 0,
            'neutral' => $total > 0 ? round(($query->clone()->where('sentiment', 'neutral')->count() / $total) * 100, 1) : 0,
            'negative' => $total > 0 ? round(($query->clone()->where('sentiment', 'negative')->count() / $total) * 100, 1) : 0,
        ];
    }
}
