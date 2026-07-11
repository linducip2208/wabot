<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Models\WaWebhook;
use App\Models\WaWebhookLog;
use App\Models\WaFlow;
use App\Services\AiService;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use App\Services\SentimentService;
use App\Services\IntentService;
use App\Services\FlowEngineService;
use App\Services\TeamInboxService;
use App\Services\SlaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected FlowEngineService $flowEngine,
        protected TeamInboxService $teamInbox,
        protected SlaService $sla,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | User-scoped webhook management (CRUD)
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $webhooks = WaWebhook::where('user_id', Auth::id())
            ->with(['logs' => fn ($q) => $q->latest()->limit(5)])
            ->latest()
            ->get();

        $recentLogs = WaWebhookLog::whereHas('webhook', fn ($q) => $q->where('user_id', Auth::id()))
            ->with('webhook')
            ->latest()
            ->limit(20)
            ->get();

        return view('webhooks.index', compact('webhooks', 'recentLogs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:message.received,message.sent,session.connected,session.disconnected,campaign.completed',
        ]);

        WaWebhook::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_values($validated['events']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.success.webhook_added'));
    }

    public function update(Request $request, WaWebhook $webhook)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:message.received,message.sent,session.connected,session.disconnected,campaign.completed',
        ]);

        $webhook->update([
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => array_values($validated['events']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', __('messages.success.webhook_updated'));
    }

    public function destroy(WaWebhook $webhook)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);
        $webhook->delete();

        return back()->with('success', __('messages.success.webhook_deleted'));
    }

    public function test(WaWebhook $webhook)
    {
        abort_if($webhook->user_id !== Auth::id(), 403);

        $payload = [
            'event' => 'test',
            'webhook_id' => $webhook->id,
            'name' => $webhook->name,
            'message' => __('messages.webhook.test_payload'),
            'timestamp' => now()->toIso8601String(),
        ];

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-WABot-Event' => 'test'])
                ->post($webhook->url, $payload);

            WaWebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => 'test',
                'response_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 2000),
                'error' => $response->failed() ? 'HTTP ' . $response->status() : null,
            ]);

            $webhook->update(['last_triggered_at' => now()]);

            if ($response->successful()) {
                return back()->with('success', __('messages.success.webhook_test_sent', ['status' => $response->status()]));
            }

            return back()->with('warning', __('messages.warning.webhook_response_http', ['status' => $response->status()]));
        } catch (\Throwable $e) {
            WaWebhookLog::create([
                'webhook_id' => $webhook->id,
                'event' => 'test',
                'response_code' => null,
                'response_body' => null,
                'error' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            return back()->with('error', __('messages.error.webhook_test_failed', ['error' => $e->getMessage()]));
        }
    }

    public function statusUpdate(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|string',
            'status' => 'required|string',
            'reason' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        $session = WaSession::where('session_id', $data['session_id'])->first();
        if (!$session) {
            return response()->json(['ok' => false, 'reason' => 'session_not_found']);
        }

        $update = ['status' => $data['status'], 'last_active_at' => now()];

        if ($data['status'] === 'connected' && $data['phone']) {
            $phone = preg_replace('/[@:].*$/', '', $data['phone']);
            $phone = preg_replace('/[^0-9]/', '', $phone);
            $update['phone'] = $phone;
        }

        if ($data['status'] === 'logged_out') {
            $update['status'] = 'disconnected';
            $update['is_active'] = false;
        }

        $session->update($update);

        \App\Models\WaSessionLog::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'event' => $data['status'],
            'phone' => $data['phone'] ?? $session->phone,
            'reason' => $data['reason'] ?? null,
            'logged_at' => now(),
        ]);

        Log::info("WhatsApp session status updated", [
            'session_id' => $data['session_id'],
            'status' => $data['status'],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json(['ok' => true]);
    }

    public function whatsapp(Request $request)
    {
        $data = $request->validate([
            'session_id' => 'required|string',
            'phone' => 'required|string',
            'display_phone' => 'nullable|string',
            'message' => 'required|string',
            'push_name' => 'nullable|string',
            'is_group' => 'boolean',
            'group_id' => 'nullable|string',
            'message_id' => 'nullable|string',
        ]);

        Log::info('WhatsApp webhook received', $data);

        $session = WaSession::where('session_id', $data['session_id'])->first();
        if (!$session) {
            return response()->json(['ok' => false, 'reason' => 'session_not_found']);
        }

        if ($session->status !== 'connected') {
            $session->update([
                'status' => 'connected',
                'last_active_at' => now(),
            ]);
        }

        $normalizedPhone = preg_replace('/@.*$/', '', $data['phone']);

        $contact = WaContact::firstOrCreate(
            ['user_id' => $session->user_id, 'phone' => $data['phone']],
            [
                'name' => $data['push_name'] ?? $normalizedPhone,
                'display_phone' => $data['display_phone'] ?? null,
            ]
        );

        if (!empty($data['push_name']) && $contact->name === $normalizedPhone) {
            $contact->update(['name' => $data['push_name']]);
        }
        if (!empty($data['display_phone']) && !$contact->display_phone && !str_contains($data['phone'], '@lid')) {
            $contact->update(['display_phone' => $data['display_phone']]);
        }

        WaMessage::create([
            'user_id' => $session->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'message' => $data['message'],
            'phone' => $normalizedPhone,
            'status' => 'delivered',
            'external_id' => $data['message_id'] ?? null,
        ]);

        // ── Sentiment analysis ───────────────────────────────────
        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $session->user_id)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey) {
            try {
                $this->sentiment->analyze($defaultAiKey, $data['message'], $contact->id, $session->user_id);
            } catch (\Throwable) {}
        }

        // ── Intent detection ─────────────────────────────────────
        try {
            $detectedIntent = $this->intent->detect($session->user_id, $data['message']);
        } catch (\Throwable) { $detectedIntent = null; }

        // ── Flow engine check ────────────────────────────────────
        $activeFlow = null;
        if (!$detectedIntent || $detectedIntent['type'] !== 'ai_agent') {
            $activeFlow = WaFlow::where('user_id', $session->user_id)
                ->where('is_active', true)
                ->where(function ($q) use ($session) {
                    $q->whereNull('trigger_keyword')->orWhere('trigger_keyword', '!=', '');
                })
                ->get()
                ->first(function ($flow) use ($data) {
                    if (!$flow->trigger_keyword) return false;
                    $msg = mb_strtolower($data['message']);
                    $kw = mb_strtolower($flow->trigger_keyword);
                    return match ($flow->trigger_match_type) {
                        'exact' => $msg === $kw,
                        'contains' => str_contains($msg, $kw),
                        'starts_with' => str_starts_with($msg, $kw),
                        default => false,
                    };
                });
        }

        // ── SLA tracking ─────────────────────────────────────────
        try {
            $this->sla->start($session->user_id, $contact->id);
        } catch (\Throwable) {}

        // ── Team inbox assignment ────────────────────────────────
        try {
            $this->teamInbox->autoAssign($contact->id, $session->id);
        } catch (\Throwable) {}

        $lastOutgoing = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->max('created_at');

        $lastOutgoing = $lastOutgoing ? \Illuminate\Support\Carbon::parse($lastOutgoing) : null;

        $canSendWelcome = !$lastOutgoing || $lastOutgoing->diffInHours(now()) >= 24;

        if ($canSendWelcome && $session->server) {
            $welcomeRule = WaAutoreply::where('user_id', $session->user_id)
                ->where('is_active', true)
                ->where('match_type', 'welcome')
                ->where(function ($query) use ($session) {
                    $query->where('session_id', $session->id)
                        ->orWhereNull('session_id');
                })
                ->first();

            if ($welcomeRule) {
                $welcomeText = $this->spintax->process($welcomeRule->reply_message, [
                    'name' => $contact->name,
                    'phone' => $normalizedPhone,
                ]);
                $result = $this->baileys->send(
                    $session->server,
                    $session->session_id,
                    $data['phone'],
                    $welcomeText
                );

                if ($result['ok'] ?? false) {
                    WaMessage::create([
                        'user_id' => $session->user_id,
                        'session_id' => $session->id,
                        'contact_id' => $contact->id,
                        'direction' => 'out',
                        'message' => $welcomeRule->reply_message,
                        'phone' => $normalizedPhone,
                        'status' => 'sent',
                    ]);
                }

                Log::info("Welcome message sent", [
                    'phone' => $normalizedPhone,
                    'name' => $contact->name,
                ]);
            }
        }

        $autoReply = $this->findAutoReply($session, $data['message']);

        // ── Flow engine ──────────────────────────────────────────
        if (!$autoReply && $activeFlow && $session->server) {
            try {
                $flowResult = $this->flowEngine->execute($activeFlow, $session, $contact, $data['message']);
                if ($flowResult) {
                    Log::info("Flow executed", ['flow_id' => $activeFlow->id, 'phone' => $normalizedPhone]);
                    return response()->json(['ok' => true, 'handler' => 'flow']);
                }
            } catch (\Throwable $e) {
                Log::error("Flow execution failed: {$e->getMessage()}");
            }
        }

        // ── Fallback: tidak ada keyword cocok → cari fallback rule ──
        if (!$autoReply && $session->server) {
            $fallback = WaAutoreply::where('user_id', $session->user_id)
                ->where('is_active', true)
                ->where('match_type', 'fallback')
                ->where(function ($query) use ($session) {
                    $query->where('session_id', $session->id)
                        ->orWhereNull('session_id');
                })
                ->orderByRaw('session_id IS NULL')
                ->first();

            if ($fallback) {
                $recentFallbacks = WaMessage::where('contact_id', $contact->id)
                    ->where('direction', 'out')
                    ->where('type', 'fallback')
                    ->where('created_at', '>', now()->subMinutes(10))
                    ->count();

                if ($recentFallbacks >= 3) {
                    Log::info("Fallback cooldown active (>3 in 10m)", ['phone' => $normalizedPhone]);
                    return response()->json(['ok' => true]);
                }

                if ($fallback->use_ai && $fallback->aiKey) {
                    $kb = $this->ai->getKnowledgeContext($session->user_id);
                    $replyText = $this->ai->send($fallback->aiKey, $data['message'], $kb ?: null);
                } else {
                    $replyText = $this->spintax->process($fallback->reply_message, [
                        'name' => $contact->name,
                        'phone' => $normalizedPhone,
                    ]);
                }

                if ($replyText) {
                    $result = $this->baileys->send(
                        $session->server,
                        $session->session_id,
                        $data['phone'],
                        $replyText
                    );

                    if ($result['ok'] ?? false) {
                        WaMessage::create([
                            'user_id' => $session->user_id,
                            'session_id' => $session->id,
                            'contact_id' => $contact->id,
                            'direction' => 'out',
                            'type' => 'fallback',
                            'message' => $replyText,
                            'phone' => $normalizedPhone,
                            'status' => 'sent',
                        ]);
                    }

                    Log::info("Fallback reply sent", [
                        'phone' => $normalizedPhone,
                        'rule_id' => $fallback->id,
                        'ai' => (bool) $fallback->use_ai,
                    ]);
                } else {
                    Log::warning("Fallback failed, no reply sent", ['phone' => $normalizedPhone]);
                }
            }

            return response()->json(['ok' => true]);
        }

        if ($autoReply && $session->server) {
            // ── AI mode (use_ai = true) ──────────────────────────
            if ($autoReply->use_ai && $autoReply->aiKey) {
                $kb = $this->ai->getKnowledgeContext($session->user_id);
                $replyText = $this->ai->send($autoReply->aiKey, $data['message'], $kb ?: null);
                if ($replyText) {
                    $savedReply = $replyText;
                } else {
                    // AI gagal → fallback ke reply_message biasa
                    Log::warning("AI auto-reply failed, falling back to text", ['keyword' => $autoReply->keyword]);
                    $replyText = $this->spintax->process($autoReply->reply_message ?: 'Maaf, saya tidak bisa menjawab saat ini.', [
                        'name' => $contact->name, 'phone' => $normalizedPhone,
                    ]);
                    $savedReply = $autoReply->reply_message ?: 'AI fallback';
                }
            } else {
                $replyText = $this->spintax->process($autoReply->reply_message, [
                    'name' => $contact->name,
                    'phone' => $normalizedPhone,
                ]);
                $savedReply = $autoReply->reply_message;
            }

            $result = $this->baileys->send(
                $session->server,
                $session->session_id,
                $data['phone'],
                $replyText
            );

            if ($result['ok'] ?? false) {
                WaMessage::create([
                    'user_id' => $session->user_id,
                    'session_id' => $session->id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'message' => $replyText,
                    'phone' => $normalizedPhone,
                    'status' => 'sent',
                ]);

                Log::info("Auto-reply sent", [
                    'keyword' => $autoReply->keyword,
                    'phone' => $normalizedPhone,
                    'ai' => $autoReply->use_ai,
                ]);
            } else {
                Log::warning("Auto-reply REST API failed, falling back to socket", [
                    'keyword' => $autoReply->keyword,
                    'phone' => $data['phone'],
                    'error' => $result['error'] ?? 'unknown',
                ]);
            }

            return response()->json(['ok' => true]);
        } elseif ($autoReply && !$session->server) {
            Log::warning("Auto-reply matched but no server configured", [
                'keyword' => $autoReply->keyword,
                'session_id' => $session->session_id,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    protected function findAutoReply(WaSession $session, string $incomingMessage): ?WaAutoreply
    {
        $rules = WaAutoreply::where('user_id', $session->user_id)
            ->where('is_active', true)
            ->whereNotIn('match_type', ['welcome', 'fallback'])
            ->where(function ($query) use ($session) {
                $query->where('session_id', $session->id)
                    ->orWhereNull('session_id');
            })
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matches($incomingMessage)) {
                return $rule;
            }
        }

        return null;
    }
}
