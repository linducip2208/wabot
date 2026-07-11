<?php

namespace App\Http\Controllers;

use App\Models\WaAutoreply;
use App\Models\WaContact;
use App\Models\WaEmailTemplate;
use App\Models\WaMessage;
use App\Models\WaSendGridAccount;
use App\Services\AiService;
use App\Services\IntentService;
use App\Services\SendGridService;
use App\Services\SentimentService;
use App\Services\SlaService;
use App\Services\SpintaxService;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendGridController extends Controller
{
    public function __construct(
        protected SendGridService $sendgrid,
        protected AiService $ai,
        protected SentimentService $sentiment,
        protected IntentService $intent,
        protected SpintaxService $spintax,
        protected SlaService $sla,
        protected TeamInboxService $teamInbox,
    ) {}

    public function index()
    {
        $accounts = WaSendGridAccount::where('user_id', Auth::id())->latest()->get();
        $templates = WaEmailTemplate::where('user_id', Auth::id())->latest()->get();
        return view('sendgrid.index', compact('accounts', 'templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'required|string|max:200',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:100',
        ]);

        WaSendGridAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'api_key_encrypted' => $validated['api_key'],
            'from_email' => $validated['from_email'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
            'is_active' => false,
        ]);

        return redirect()->route('sendgrid.index')->with('success', __('messages.success.sendgrid_added'));
    }

    public function connect(WaSendGridAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $verified = $this->sendgrid->verifyApiKey($account);

        if (!$verified) {
            return back()->with('error', __('messages.error.sendgrid_connection_failed'));
        }

        $account->update([
            'is_active' => true,
            'connected_at' => now(),
        ]);

        return back()->with('success', __('messages.success.sendgrid_connected'));
    }

    public function disconnect(WaSendGridAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $account->update(['is_active' => false]);

        return back()->with('success', __('messages.success.sendgrid_disconnected'));
    }

    public function testSend(Request $request, WaSendGridAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'to' => 'required|email|max:255',
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ]);

        $result = $this->sendgrid->sendEmail($account, $validated['to'], $validated['subject'], $validated['body']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        return back()->with('error', __('messages.error.sendgrid_failed', ['error' => $result['error'] ?? 'Unknown']));
    }

    public function update(Request $request, WaSendGridAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:200',
            'from_email' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:100',
        ]);

        $update = [
            'name' => $validated['name'],
            'from_email' => $validated['from_email'] ?? null,
            'from_name' => $validated['from_name'] ?? null,
        ];

        if (!empty($validated['api_key'])) {
            $update['api_key_encrypted'] = $validated['api_key'];
        }

        $account->update($update);

        return redirect()->route('sendgrid.index')->with('success', __('messages.success.sendgrid_updated'));
    }

    public function destroy(WaSendGridAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('sendgrid.index')->with('success', __('messages.success.sendgrid_deleted'));
    }

    public function webhook(Request $request)
    {
        $events = $request->all();

        Log::info('SendGrid webhook received', $events);

        foreach ($events as $event) {
            $eventType = $event['event'] ?? 'unknown';
            $email = $event['email'] ?? null;
            $sgMessageId = $event['sg_message_id'] ?? null;
            $timestamp = $event['timestamp'] ?? null;

            if (!$email) continue;

            $senderId = 'email:' . $email;

            $msg = null;
            if ($sgMessageId) {
                $msg = WaMessage::where('external_id', $sgMessageId)->first();
            }

            $userId = $msg ? $msg->user_id : null;

            if (!$userId) {
                $account = WaSendGridAccount::where('is_active', true)->first();
                if (!$account) continue;
                $userId = $account->user_id;
            }

            $contact = WaContact::firstOrCreate(
                ['user_id' => $userId, 'phone' => $senderId],
                ['name' => $email, 'display_phone' => $email]
            );

            $status = match ($eventType) {
                'bounce' => 'bounced',
                'dropped' => 'dropped',
                'spamreport' => 'spam',
                'deferred' => 'deferred',
                'open' => 'read',
                'click' => 'read',
                'delivered' => 'delivered',
                default => null,
            };

            if ($sgMessageId && $status) {
                WaMessage::where('external_id', $sgMessageId)->update(['status' => $status]);
            }

            if ($eventType === 'inbound' || $eventType === 'processed' || $eventType === 'delivered') {
                if ($sgMessageId) {
                    WaMessage::where('external_id', $sgMessageId)
                        ->update(['status' => $status ?? 'delivered']);
                }
            }

            // ── Auto-Reply Pipeline for Inbound Email ──
            if ($eventType === 'inbound' && !empty($event['subject']) && !empty($event['text'])) {
                $this->processInboundEmail($userId, $contact, $event);
            }

            Log::info("SendGrid event: {$eventType}", ['email' => $email, 'status' => $status]);
        }

        return response('ok');
    }

    /**
     * 8-step auto-reply pipeline for inbound email.
     */
    protected function processInboundEmail(int $userId, WaContact $contact, array $event): void
    {
        $incomingMessage = $event['text'] ?? '';
        $subject = $event['subject'] ?? '';
        $fromEmail = $event['from'] ?? $contact->phone;

        Log::info('SendGrid inbound pipeline', ['email' => $fromEmail, 'subject' => $subject]);

        $replyText = null;

        // Step 1: Sentiment analysis
        $sentiment = $this->sentiment->analyze($contact, $incomingMessage);

        // Step 2: Intent detection
        $intent = $this->intent->detect($contact, $incomingMessage);

        // Step 3: SLA escalation
        $this->sla->escalateIfNeeded($userId, $contact, $incomingMessage);

        // Step 4: Team inbox routing
        $this->teamInbox->routeToTeam($userId, $contact, $incomingMessage, 'email');

        // Step 5: Welcome message (first-time contact)
        $autoreply = WaAutoreply::where('user_id', $userId)
            ->where('is_active', true)
            ->where('channel', 'email')
            ->where('match_type', 'welcome')
            ->first();
        if ($autoreply && WaMessage::where('contact_id', $contact->id)->where('direction', 'in')->count() <= 1) {
            $replyText = $this->spintax->process($autoreply->reply, ['name' => $contact->name]);
        }

        // Step 6: AI auto-reply
        if (!$replyText) {
            $autoReply = WaAutoreply::where('user_id', $userId)
                ->where('is_active', true)
                ->where('channel', 'email')
                ->where('match_type', 'keyword')
                ->get();
            foreach ($autoReply as $ar) {
                if ($ar->is_exact && mb_strtolower(trim($incomingMessage)) === mb_strtolower(trim($ar->keyword))) {
                    $replyText = $this->spintax->process($ar->reply, ['name' => $contact->name]);
                    break;
                }
                if (!$ar->is_exact && str_contains(mb_strtolower($incomingMessage), mb_strtolower($ar->keyword))) {
                    $replyText = $this->spintax->process($ar->reply, ['name' => $contact->name]);
                    break;
                }
            }
        }

        // Step 7: AI-powered fallback
        if (!$replyText) {
            $aiReply = WaAutoreply::where('user_id', $userId)
                ->where('is_active', true)
                ->where('channel', 'email')
                ->where('match_type', 'ai')
                ->first();
            if ($aiReply) {
                $aiKey = $aiReply->aiKey;
                if ($aiKey) {
                    try {
                        $replyText = $this->ai->send($aiKey, $incomingMessage);
                    } catch (\Throwable $e) {
                        Log::error('SendGrid AI reply failed: ' . $e->getMessage());
                    }
                }
            }
        }

        // Step 8: Generic fallback
        if (!$replyText) {
            $fallback = WaAutoreply::where('user_id', $userId)
                ->where('is_active', true)
                ->where('channel', 'email')
                ->where('match_type', 'fallback')
                ->first();
            if ($fallback) {
                $replyText = $this->spintax->process($fallback->reply, ['name' => $contact->name]);
            }
        }

        // Send reply if we have one
        if ($replyText) {
            $account = WaSendGridAccount::where('user_id', $userId)->where('is_active', true)->first();
            if ($account) {
                $replySubject = 'Re: ' . $subject;
                $result = $this->sendgrid->sendEmail($account, $fromEmail, $replySubject, $replyText);
                if ($result['ok'] ?? false) {
                    WaMessage::create([
                        'user_id' => $userId,
                        'contact_id' => $contact->id,
                        'direction' => 'out',
                        'channel' => 'email',
                        'message' => $replyText,
                        'phone' => $contact->phone,
                        'status' => 'sent',
                    ]);
                }
            }
        }
    }

    // ── Email Template CRUD ──────────────────────────────────────

    public function templateStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string|max:100000',
            'variables' => 'nullable|json',
        ]);

        WaEmailTemplate::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'variables' => isset($validated['variables']) ? json_decode($validated['variables'], true) : null,
        ]);

        return redirect()->route('sendgrid.index')->with('success', __('messages.success.email_template_added'));
    }

    public function templateUpdate(Request $request, WaEmailTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string|max:100000',
            'variables' => 'nullable|json',
        ]);

        $template->update([
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'variables' => isset($validated['variables']) ? json_decode($validated['variables'], true) : null,
        ]);

        return redirect()->route('sendgrid.index')->with('success', __('messages.success.email_template_updated'));
    }

    public function templateDestroy(WaEmailTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);
        $template->delete();
        return redirect()->route('sendgrid.index')->with('success', __('messages.success.email_template_deleted'));
    }
}
