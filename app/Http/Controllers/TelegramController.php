<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Models\WaTelegramAccount;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramService $telegram,
    ) {}

    public function index()
    {
        $accounts = WaTelegramAccount::where('user_id', Auth::id())->latest()->get();
        return view('telegram.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bot_token' => 'required|string|max:200',
        ]);

        $account = WaTelegramAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'bot_token' => $validated['bot_token'],
            'status' => 'disconnected',
        ]);

        return redirect()->route('telegram.index')->with('success', __('messages.success.telegram_added'));
    }

    public function connect(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $me = $this->telegram->getMe($account);

        if (!$me) {
            return back()->with('error', __('messages.error.telegram_connection_failed'));
        }

        $webhookUrl = route('webhook.telegram', ['account' => $account->id]);

        $this->telegram->setWebhook($account, $webhookUrl);

        $account->update([
            'bot_username' => $me['username'] ?? null,
            'bot_id' => (string) ($me['id'] ?? ''),
            'status' => 'connected',
            'is_active' => true,
            'last_active_at' => now(),
        ]);

        return back()->with('success', __('messages.success.telegram_connected', ['username' => ($me['username'] ?? 'unknown')]));
    }

    public function disconnect(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $this->telegram->deleteWebhook($account);

        $account->update(['status' => 'disconnected', 'is_active' => false]);

        return back()->with('success', __('messages.success.telegram_disconnected'));
    }

    public function testSend(Request $request, WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'chat_id' => 'required|string|max:50',
            'message' => 'required|string|max:1000',
        ]);

        $result = $this->telegram->sendMessage($account, $validated['chat_id'], $validated['message']);

        if ($result['ok'] ?? false) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        return back()->with('error', __('messages.error.telegram_failed', ['error' => ($result['description'] ?? 'Unknown')]));
    }

    public function destroy(WaTelegramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('telegram.index')->with('success', __('messages.success.telegram_deleted'));
    }

    public function webhook(Request $request, WaTelegramAccount $account)
    {
        $update = $request->all();

        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) return response('ok');

        $chatId = $message['chat']['id'] ?? null;
        $text = $message['text'] ?? '';
        $from = $message['from'] ?? [];
        $senderName = $from['first_name'] ?? 'Telegram User';
        $senderId = 'tg:' . ($from['id'] ?? $chatId);

        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => $senderId],
            ['name' => $senderName, 'display_phone' => '@' . ($from['username'] ?? $chatId)]
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'telegram',
            'message' => $text,
            'phone' => $senderId,
            'status' => 'delivered',
        ]);

        $reply = __('messages.auto_reply.telegram_received');

        $this->telegram->sendMessage($account, $chatId, $reply);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'telegram',
            'message' => $reply,
            'phone' => $senderId,
            'status' => 'sent',
        ]);

        return response('ok');
    }
}
