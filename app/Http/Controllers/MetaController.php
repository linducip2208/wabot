<?php

namespace App\Http\Controllers;

use App\Models\WaMetaAccount;
use App\Models\WaSession;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MetaController extends Controller
{
    public function __construct(
        protected MetaApiService $meta,
    ) {}

    public function index()
    {
        $accounts = WaMetaAccount::where('user_id', Auth::id())
            ->with('sessions')
            ->latest()
            ->get();

        return view('meta.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:50',
            'access_token' => 'required|string|max:500',
            'waba_id' => 'nullable|string|max:50',
            'app_id' => 'nullable|string|max:50',
            'app_secret' => 'nullable|string|max:200',
            'webhook_verify_token' => 'nullable|string|max:100',
        ]);

        $account = WaMetaAccount::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'phone_number_id' => $validated['phone_number_id'],
            'access_token' => $validated['access_token'],
            'waba_id' => $validated['waba_id'] ?? null,
            'app_id' => $validated['app_id'] ?? null,
            'app_secret' => $validated['app_secret'] ?? null,
            'webhook_verify_token' => $validated['webhook_verify_token'] ?? null,
            'status' => 'disconnected',
        ]);

        $info = $this->meta->getBusinessInfo($account);
        if (!empty($info['data'][0])) {
            $account->update([
                'business_name' => $info['data'][0]['about'] ?? null,
            ]);
        }

        if (!$account->waba_id) {
            $wabaId = $this->meta->getWabaId($account);
            if ($wabaId) {
                $account->update(['waba_id' => $wabaId]);
            }
        }

        return redirect()->route('meta.index')->with('success', __('messages.success.meta_added'));
    }

    public function update(Request $request, WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number_id' => 'required|string|max:50',
            'access_token' => 'required|string|max:500',
            'waba_id' => 'nullable|string|max:50',
            'app_id' => 'nullable|string|max:50',
            'app_secret' => 'nullable|string|max:200',
            'webhook_verify_token' => 'nullable|string|max:100',
        ]);

        $account->update($validated);

        return back()->with('success', __('messages.success.meta_updated'));
    }

    public function destroy(WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        WaSession::where('meta_account_id', $account->id)->update([
            'meta_account_id' => null,
            'status' => 'disconnected',
        ]);

        $account->delete();

        return redirect()->route('meta.index')->with('success', __('messages.success.meta_deleted'));
    }

    public function connect(WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        try {
            $wabaId = $account->waba_id ?: $this->meta->getWabaId($account);
            if ($wabaId) {
                $account->update(['waba_id' => $wabaId]);
            }

            $account->update([
                'status' => 'connected',
                'is_active' => true,
                'last_active_at' => now(),
            ]);

            return back()->with('success', __('messages.success.meta_connected'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.error.meta_connection_failed', ['error' => $e->getMessage()]));
        }
    }

    public function disconnect(WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $account->update(['status' => 'disconnected', 'is_active' => false]);

        WaSession::where('meta_account_id', $account->id)->update([
            'status' => 'disconnected',
        ]);

        return back()->with('success', __('messages.success.meta_disconnected'));
    }

    public function sessionStore(Request $request, WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $session = WaSession::create([
            'user_id' => Auth::id(),
            'meta_account_id' => $account->id,
            'session_id' => 'meta_' . uniqid('', true),
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? $account->phone_number,
            'channel' => 'meta',
            'status' => 'connected',
            'is_active' => true,
        ]);

        return back()->with('success', __('messages.success.meta_session_created'));
    }

    public function testSend(Request $request, WaMetaAccount $account)
    {
        abort_if($account->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'phone' => 'required|string|max:30',
            'message' => 'required|string|max:1000',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
        $result = $this->meta->sendText($account, $phone, $validated['message']);

        if (!empty($result['messages'])) {
            return back()->with('success', __('messages.success.test_message_sent'));
        }

        $error = $result['error']['message'] ?? $result['error']['error_user_msg'] ?? 'Unknown error';
        return back()->with('error', __('messages.error.meta_send_failed', ['error' => $error]));
    }
}
