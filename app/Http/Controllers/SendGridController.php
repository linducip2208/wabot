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

            Log::info("SendGrid event: {$eventType}", ['email' => $email, 'status' => $status]);
        }

        return response('ok');
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
