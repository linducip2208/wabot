<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaTwilioAccount;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use App\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    public function __construct(
        protected TwilioService $twilio,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaTwilioAccount::where('user_id', Auth::id())->latest()->get();
        return view('twilio.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_sid' => 'required|string|max:100',
            'auth_token' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:30',
        ]);

        WaTwilioAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'account_sid_encrypted' => $validated['account_sid'],
            'auth_token_encrypted' => $validated['auth_token'],
            'phone_number' => $validated['phone_number'] ?? null,
            'is_active' => false,
        ]);

        return redirect()->route('twilio.index')->with('success', __('messages.success.twilio_added'));
    }

    public function connect(WaTwilioAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $verified = $this->twilio->verifyCredentials($account);

        if (!$verified) {
            return back()->with('error', __('messages.error.twilio_connection_failed'));
        }

        $account->update([
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return back()->with('success', __('messages.success.twilio_connected'));
    }

    public function disconnect(WaTwilioAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $account->update(['is_active' => false]);

        return back()->with('success', __('messages.success.twilio_disconnected'));
    }

    public function testSend(Request $request, WaTwilioAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'to' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
            'via_whatsapp' => 'nullable|boolean',
        ]);

        $method = !empty($validated['via_whatsapp']) ? 'sendWhatsApp' : 'sendSms';
        $result = $this->twilio->$method($account, $validated['to'], $validated['message']);

        if (!($result['error'] ?? false) && empty($result['error_code']) && empty($result['code'])) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        $errMsg = $result['error_message'] ?? $result['message'] ?? 'Unknown';
        return back()->with('error', __('messages.error.twilio_failed', ['error' => $errMsg]));
    }

    public function update(Request $request, WaTwilioAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'account_sid' => 'nullable|string|max:100',
            'auth_token' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $update = ['name' => $validated['name'], 'phone_number' => $validated['phone_number'] ?? null];

        if (!empty($validated['account_sid'])) {
            $update['account_sid_encrypted'] = $validated['account_sid'];
        }
        if (!empty($validated['auth_token'])) {
            $update['auth_token_encrypted'] = $validated['auth_token'];
        }

        $account->update($update);

        return redirect()->route('twilio.index')->with('success', __('messages.success.twilio_updated'));
    }

    public function destroy(WaTwilioAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('twilio.index')->with('success', __('messages.success.twilio_deleted'));
    }

    public function webhook(Request $request)
    {
        $body = $request->all();

        Log::info('Twilio webhook received', $body);

        $messageSid = $body['MessageSid'] ?? null;
        $smsStatus = $body['SmsStatus'] ?? null;
        $from = $body['From'] ?? null;
        $to = $body['To'] ?? null;
        $bodyText = $body['Body'] ?? null;

        if ($messageSid && $smsStatus && $smsStatus !== 'received') {
            WaMessage::where('external_id', $messageSid)
                ->update(['status' => $smsStatus]);
            return response('ok');
        }

        if (!$from || !$bodyText) return response('ok');

        $cleanedPhone = str_replace('whatsapp:', '', $from);

        $senderId = 'sms:' . $cleanedPhone;

        $account = WaTwilioAccount::where('phone_number', $to)
            ->where('is_active', true)
            ->first();

        if (!$account) {
            $msg = WaMessage::where('phone', $to)
                ->where('direction', 'out')
                ->latest()
                ->first();
            $account = $msg ? WaTwilioAccount::where('user_id', $msg->user_id)->where('is_active', true)->first() : null;
        }

        if (!$account) {
            $account = WaTwilioAccount::where('is_active', true)->first();
        }

        if (!$account) return response('ok');

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $from, 'display_phone' => $from]
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'sms',
            'channel' => 'sms',
            'message' => $bodyText,
            'phone' => $senderId,
            'external_id' => $messageSid,
            'status' => 'delivered',
        ]);

        $userId = $account->user_id;

        $defaultAiKey = \App\Models\WaAiKey::where('user_id', $userId)
            ->where('is_active', true)
            ->first();
        if ($defaultAiKey && $bodyText) {
            try {
                $this->sentiment->analyze($defaultAiKey, $bodyText, $contact->id, $userId);
            } catch (\Throwable) {}
        }

        try {
            $this->sla->start($userId, $contact->id);
        } catch (\Throwable) {}

        try {
            $this->teamInbox->autoAssign($contact->id, 0);
        } catch (\Throwable) {}

        $this->checkWelcome($account, $contact, $from);

        try {
            $detectedIntent = $this->intent->detect($userId, $bodyText, 'sms');
        } catch (\Throwable) {
            $detectedIntent = null;
        }

        if ($detectedIntent && $detectedIntent['type'] === 'ai_agent' && $defaultAiKey) {
            $this->handleAiAgent($account, $contact, $from, $bodyText, $defaultAiKey);
            return response('ok');
        }

        if ($this->handleKeywordReply($account, $contact, $from, $bodyText)) {
            return response('ok');
        }

        $this->handleFallback($account, $contact, $from, $bodyText);

        return response('ok');
    }

    protected function checkWelcome(WaTwilioAccount $account, WaContact $contact, string $to): void
    {
        $lastOutgoing = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->max('created_at');

        $lastOutgoing = $lastOutgoing ? \Illuminate\Support\Carbon::parse($lastOutgoing) : null;
        $canSendWelcome = !$lastOutgoing || $lastOutgoing->diffInHours(now()) >= 24;

        if (!$canSendWelcome) return;

        $welcomeRule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'welcome')
            ->whereNull('session_id')
            ->first();

        if (!$welcomeRule) return;

        $welcomeText = $this->spintax->process($welcomeRule->reply_message, [
            'name' => $contact->name,
            'phone' => $to,
        ]);

        $result = $this->twilio->sendSms($account, $to, $welcomeText);

        if (!($result['error'] ?? false) && empty($result['error_code']) && empty($result['code'])) {
            WaMessage::create([
                'user_id' => $account->user_id,
                'contact_id' => $contact->id,
                'direction' => 'out',
                'type' => 'sms',
                'channel' => 'sms',
                'message' => $welcomeRule->reply_message,
                'phone' => $contact->phone,
                'status' => 'sent',
            ]);

            Log::info("SMS welcome sent", ['to' => $to, 'name' => $contact->name]);
        }
    }

    protected function handleAiAgent(WaTwilioAccount $account, WaContact $contact, string $to, string $text, \App\Models\WaAiKey $aiKey): void
    {
        try {
            $kb = $this->ai->getKnowledgeContext($account->user_id);
            $reply = $this->ai->send($aiKey, $text, $kb ?: null);

            if ($reply) {
                $result = $this->twilio->sendSms($account, $to, $reply);

                WaMessage::create([
                    'user_id' => $account->user_id,
                    'contact_id' => $contact->id,
                    'direction' => 'out',
                    'type' => 'sms',
                    'channel' => 'sms',
                    'message' => $reply,
                    'phone' => $contact->phone,
                    'status' => (!($result['error'] ?? false)) ? 'sent' : 'failed',
                ]);

                Log::info("SMS AI agent reply sent", ['to' => $to]);
            }
        } catch (\Exception $e) {
            Log::error('SMS AI agent failed: ' . $e->getMessage());
        }
    }

    protected function handleKeywordReply(WaTwilioAccount $account, WaContact $contact, string $to, string $text): bool
    {
        $rule = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->whereNotIn('match_type', ['welcome', 'fallback'])
            ->whereNull('session_id')
            ->get()
            ->first(fn($r) => $r->matches($text));

        if (!$rule) return false;

        $replyText = $this->spintax->process($rule->reply_message, [
            'name' => $contact->name,
            'phone' => $to,
        ]);

        $result = $this->twilio->sendSms($account, $to, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'sms',
            'channel' => 'sms',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => (!($result['error'] ?? false)) ? 'sent' : 'failed',
        ]);

        Log::info("SMS keyword auto-reply sent", ['to' => $to, 'keyword' => $rule->keyword]);

        try {
            $this->sla->recordResponse($account->user_id, $contact->id);
        } catch (\Throwable) {}

        return true;
    }

    protected function handleFallback(WaTwilioAccount $account, WaContact $contact, string $to, string $text): void
    {
        $fallback = WaAutoreply::where('user_id', $account->user_id)
            ->where('is_active', true)
            ->where('match_type', 'fallback')
            ->whereNull('session_id')
            ->first();

        if (!$fallback) return;

        $recentFallbacks = WaMessage::where('contact_id', $contact->id)
            ->where('direction', 'out')
            ->where('type', 'fallback')
            ->where('created_at', '>', now()->subMinutes(10))
            ->count();

        if ($recentFallbacks >= 3) return;

        $replyText = $this->spintax->process($fallback->reply_message, [
            'name' => $contact->name,
            'phone' => $to,
        ]);

        $result = $this->twilio->sendSms($account, $to, $replyText);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'fallback',
            'channel' => 'sms',
            'message' => $replyText,
            'phone' => $contact->phone,
            'status' => (!($result['error'] ?? false)) ? 'sent' : 'failed',
        ]);

        Log::info("SMS fallback sent", ['to' => $to]);
    }
}
