<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaContact;
use App\Services\BaileysService;
use App\Services\SpintaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected BaileysService $baileys,
        protected SpintaxService $spintax,
    ) {}

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

        $autoReply = $this->findAutoReply($session, $data['message']);

        if ($autoReply && $session->server) {
            $lastReply = WaMessage::where('contact_id', $contact->id)
                ->where('direction', 'out')
                ->where('created_at', '>', now()->subSeconds(10))
                ->exists();

            if ($lastReply) {
                Log::info("Auto-reply skipped (cooldown)", ['phone' => $normalizedPhone]);
                return response()->json(['ok' => true, 'skipped' => 'cooldown']);
            }
            $replyText = $this->spintax->process($autoReply->reply_message, [
                'name' => $contact->name,
                'phone' => $normalizedPhone,
            ]);
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
                    'message' => $autoReply->reply_message,
                    'phone' => $normalizedPhone,
                    'status' => 'sent',
                ]);

                Log::info("Auto-reply sent", [
                    'keyword' => $autoReply->keyword,
                    'phone' => $normalizedPhone,
                ]);
            } else {
                Log::warning("Auto-reply failed to send", [
                    'keyword' => $autoReply->keyword,
                    'phone' => $data['phone'],
                    'error' => $result['error'] ?? 'unknown',
                ]);
            }
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
