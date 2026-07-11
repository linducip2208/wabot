<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use App\Models\WaInstagramAccount;
use App\Models\WaMessage;
use App\Models\WaSession;
use App\Services\InstagramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstagramController extends Controller
{
    public function __construct(
        protected InstagramService $instagram,
    ) {}

    public function index()
    {
        $accounts = WaInstagramAccount::where('user_id', Auth::id())->latest()->get();
        return view('instagram.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'app_id' => 'required|string|max:100',
            'app_secret' => 'required|string|max:200',
            'webhook_verify_token' => 'nullable|string|max:100',
        ]);

        WaInstagramAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'app_id' => $validated['app_id'],
            'app_secret' => $validated['app_secret'],
            'webhook_verify_token' => $validated['webhook_verify_token'] ?? null,
            'status' => 'disconnected',
        ]);

        return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_added'));
    }

    public function connect(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $redirectUri = route('instagram.callback');
        $url = $this->instagram->getAuthUrl($account, $redirectUri);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->get('code');

        if (!$code) {
            return redirect()->route('instagram.index')->with('error', __('messages.error.instagram_authorization_cancelled'));
        }

        $accounts = WaInstagramAccount::where('user_id', Auth::id())->get();

        foreach ($accounts as $account) {
            $tokenData = $this->instagram->exchangeToken($account, $code, route('instagram.callback'));

            if ($tokenData && isset($tokenData['access_token'])) {
                $account->update([
                    'access_token' => $tokenData['access_token'],
                    'instagram_id' => $tokenData['user_id'] ?? null,
                    'status' => 'connected',
                    'last_active_at' => now(),
                ]);

                $longToken = $this->instagram->getLongLivedToken($account);
                if ($longToken && isset($longToken['access_token'])) {
                    $account->update(['access_token' => $longToken['access_token']]);
                }

                return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_connected'));
            }
        }

        return redirect()->route('instagram.index')->with('error', __('messages.error.instagram_connection_failed'));
    }

    public function disconnect(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->update(['status' => 'disconnected', 'is_active' => false]);
        return back()->with('success', __('messages.success.instagram_disconnected'));
    }

    public function destroy(WaInstagramAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);
        $account->delete();
        return redirect()->route('instagram.index')->with('success', __('messages.success.instagram_deleted'));
    }

    public function webhook(Request $request)
    {
        if ($request->method() === 'GET') {
            return $this->verifyWebhook($request);
        }

        $body = $request->all();

        foreach ($body['entry'] ?? [] as $entry) {
            foreach ($entry['messaging'] ?? [] as $messaging) {
                $sender = $messaging['sender']['id'] ?? null;
                $recipient = $messaging['recipient']['id'] ?? null;
                $message = $messaging['message']['text'] ?? null;

                if ($sender && $recipient && $message) {
                    $account = WaInstagramAccount::where('instagram_id', $recipient)->first();
                    if ($account) {
                        $this->handleDM($account, $sender, $message);
                    }
                }
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') === 'comments') {
                    $this->handleComment($change['value'] ?? []);
                }
            }
        }

        return response('ok', 200);
    }

    protected function verifyWebhook(Request $request)
    {
        $mode = $request->get('hub_mode');
        $token = $request->get('hub_verify_token');
        $challenge = $request->get('hub_challenge');

        $accounts = WaInstagramAccount::whereNotNull('webhook_verify_token')->get();
        foreach ($accounts as $account) {
            if ($mode === 'subscribe' && $token === $account->webhook_verify_token) {
                return response($challenge, 200);
            }
        }

        return response('Verification failed', 403);
    }

    protected function handleDM(WaInstagramAccount $account, string $senderId, string $text): void
    {
        $contact = WaContact::firstOrCreate(
            ['user_id' => $account->user_id, 'phone' => 'ig:' . $senderId],
            ['name' => 'IG: ' . $senderId, 'display_phone' => 'IG DM']
        );

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'in',
            'type' => 'instagram',
            'message' => $text,
            'phone' => 'ig:' . $senderId,
            'status' => 'delivered',
        ]);

        $reply = __('messages.auto_reply.instagram_received');
        $this->instagram->sendDM($senderId, $account->access_token, $reply);

        WaMessage::create([
            'user_id' => $account->user_id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'instagram',
            'message' => $reply,
            'phone' => 'ig:' . $senderId,
            'status' => 'sent',
        ]);
    }

    protected function handleComment(array $value): void
    {
        // Handle Instagram comments - store as message
        // This is a simplified handler
    }
}
