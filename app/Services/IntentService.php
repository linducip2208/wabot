<?php

namespace App\Services;

use App\Models\WaIntentConfig;
use App\Models\WaAiAgent;
use App\Models\WaAiKey;

class IntentService
{
    /**
     * Deteksi intent dari pesan masuk. Priority: AI agent trigger keywords > intent config keywords.
     */
    public function detect(int $userId, string $message, ?string $channel = null): ?array
    {
        $msg = mb_strtolower($message);

        $agentsQuery = WaAiAgent::where('user_id', $userId)
            ->where('is_active', true)
            ->whereNotNull('trigger_keywords');

        if ($channel) {
            $agentsQuery->where(function ($q) use ($channel) {
                $q->whereNull('channels')
                  ->orWhereJsonContains('channels', $channel);
            });
        }

        $agent = $agentsQuery->get()
            ->first(function ($agent) use ($msg) {
                $keywords = array_map('trim', explode(',', mb_strtolower($agent->trigger_keywords)));
                foreach ($keywords as $kw) {
                    if ($kw && str_contains($msg, $kw)) return true;
                }
                return false;
            });

        if ($agent) {
            return [
                'type' => 'ai_agent',
                'agent_id' => $agent->id,
                'agent' => $agent,
                'label' => $agent->role,
            ];
        }

        // 2. Cek intent config
        $intent = WaIntentConfig::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->first(fn($i) => $i->hasKeyword($message));

        if ($intent) {
            return [
                'type' => 'intent',
                'intent_id' => $intent->id,
                'intent' => $intent,
                'label' => $intent->intent_label,
                'auto_reply' => $intent->auto_reply,
            ];
        }

        return null;
    }

    /**
     * Pilih AI agent berdasarkan intent atau default.
     */
    public function selectAgent(int $userId, ?string $message = null): ?WaAiAgent
    {
        if ($message) {
            $result = $this->detect($userId, $message);
            if ($result && $result['type'] === 'ai_agent') {
                return $result['agent'];
            }
        }

        return WaAiAgent::where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Bangun system prompt untuk agent tertentu.
     */
    public function buildAgentPrompt(WaAiAgent $agent): string
    {
        $base = $agent->personality_prompt;
        if (!$base) return '';

        return "Kamu berperan sebagai {$agent->name} ({$agent->role}).\n{$base}";
    }
}
