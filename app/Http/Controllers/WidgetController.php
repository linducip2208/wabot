<?php

namespace App\Http\Controllers;

use App\Models\WaWidget;
use App\Models\WaWidgetLead;
use App\Services\ChannelRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;

class WidgetController extends Controller
{
    public function index()
    {
        $widgets = WaWidget::where('user_id', auth()->id())->latest()->get();
        $connectedAccounts = $this->getConnectedAccounts();
        return view('widgets.index', compact('widgets', 'connectedAccounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'greeting_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'button_icon' => 'nullable|string|max:50',
            'channels' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->filled('channels') && is_string($request->channels)) {
            $channels = json_decode($request->channels, true);
            $connectedChannelTypes = array_keys($this->getConnectedAccounts());
            foreach ($channels as $ch) {
                if (!in_array($ch['type'] ?? null, $connectedChannelTypes)) {
                    return back()->withInput()->with('error', __('messages.error.channel_not_connected', ['channel' => $ch['type'] ?? 'unknown']));
                }
            }
            $validated['channels'] = $channels;
        }

        $widget = WaWidget::create($validated);

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_created'));
    }

    public function update(Request $request, WaWidget $widget)
    {
        if ($widget->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'greeting_message' => 'nullable|string|max:500',
            'offline_message' => 'nullable|string|max:500',
            'theme_color' => 'nullable|string|max:7',
            'position' => 'nullable|in:bottom-right,bottom-left',
            'button_icon' => 'nullable|string|max:50',
            'channels' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('channels') && is_string($request->channels)) {
            $channels = json_decode($request->channels, true);
            $connectedChannelTypes = array_keys($this->getConnectedAccounts());
            foreach ($channels as $ch) {
                if (!in_array($ch['type'] ?? null, $connectedChannelTypes)) {
                    return back()->withInput()->with('error', __('messages.error.channel_not_connected', ['channel' => $ch['type'] ?? 'unknown']));
                }
            }
            $validated['channels'] = $channels;
        }

        $widget->update($validated);

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_updated'));
    }

    public function destroy(WaWidget $widget)
    {
        if ($widget->user_id !== auth()->id()) {
            abort(403);
        }

        $widget->delete();

        return redirect()->route('widgets.index')->with('success', __('messages.success.widget_deleted'));
    }

    public function embedScript($embedKey)
    {
        $widget = WaWidget::where('embed_key', $embedKey)->where('is_active', true)->firstOrFail();

        $channels = $widget->channels ?? [];

        $js = view('widgets.embed', [
            'widget' => $widget,
            'channels' => $channels,
        ])->render();

        return Response::make($js, 200, ['Content-Type' => 'application/javascript']);
    }

    public function storeLead(Request $request, $embedKey)
    {
        $widget = WaWidget::where('embed_key', $embedKey)->where('is_active', true)->firstOrFail();

        $key = 'widget-lead:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return response()->json(['error' => 'Too many requests'], 429);
        }
        RateLimiter::hit($key, 300);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $lead = $widget->leads()->create([
            'name' => $validated['name'],
            'message' => $validated['message'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true, 'id' => $lead->id]);
    }

    protected function getConnectedAccounts(): array
    {
        $userId = auth()->id();
        $accounts = [];

        $sessions = \App\Models\WaSession::where('user_id', $userId)
            ->where('status', 'connected')->exists();
        if ($sessions) {
            $accounts['whatsapp'] = ['label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp'];
        }

        $igAccounts = \App\Models\WaInstagramAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($igAccounts->isNotEmpty()) {
            $accounts['instagram'] = [];
            foreach ($igAccounts as $acc) {
                $accounts['instagram'][] = ['id' => $acc->id, 'name' => $acc->name, 'label' => $acc->name];
            }
        }

        $tgAccounts = \App\Models\WaTelegramAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($tgAccounts->isNotEmpty()) {
            $accounts['telegram'] = [];
            foreach ($tgAccounts as $acc) {
                $accounts['telegram'][] = ['id' => $acc->id, 'name' => $acc->name, 'bot_username' => $acc->bot_username];
            }
        }

        $fbAccounts = \App\Models\WaFacebookAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($fbAccounts->isNotEmpty()) {
            $accounts['facebook'] = [];
            foreach ($fbAccounts as $acc) {
                $accounts['facebook'][] = ['id' => $acc->id, 'name' => $acc->name, 'page_id' => $acc->page_id];
            }
        }

        $gbmAccounts = \App\Models\WaGbmAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($gbmAccounts->isNotEmpty()) {
            $accounts['gbm'] = [];
            foreach ($gbmAccounts as $acc) {
                $accounts['gbm'][] = ['id' => $acc->id, 'name' => $acc->name, 'brand_id' => $acc->brand_id];
            }
        }

        $dcAccounts = \App\Models\WaDiscordAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($dcAccounts->isNotEmpty()) {
            $accounts['discord'] = [];
            foreach ($dcAccounts as $acc) {
                $accounts['discord'][] = ['id' => $acc->id, 'name' => $acc->name, 'bot_name' => $acc->bot_name];
            }
        }

        $ttAccounts = \App\Models\WaTiktokAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($ttAccounts->isNotEmpty()) {
            $accounts['tiktok'] = [];
            foreach ($ttAccounts as $acc) {
                $accounts['tiktok'][] = ['id' => $acc->id, 'name' => $acc->name, 'open_id' => $acc->open_id];
            }
        }

        $lineAccounts = \App\Models\WaLineAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($lineAccounts->isNotEmpty()) {
            $accounts['line'] = [];
            foreach ($lineAccounts as $acc) {
                $accounts['line'][] = ['id' => $acc->id, 'name' => $acc->name, 'channel_id' => $acc->channel_id];
            }
        }

        $twAccounts = \App\Models\WaTwitterAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($twAccounts->isNotEmpty()) {
            $accounts['twitter'] = [];
            foreach ($twAccounts as $acc) {
                $accounts['twitter'][] = ['id' => $acc->id, 'name' => $acc->name, 'username' => $acc->username];
            }
        }

        $smsAccounts = \App\Models\WaTwilioAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($smsAccounts->isNotEmpty()) {
            $accounts['sms'] = [];
            foreach ($smsAccounts as $acc) {
                $accounts['sms'][] = ['id' => $acc->id, 'name' => $acc->name, 'phone_number' => $acc->phone_number];
            }
        }

        $emailAccounts = \App\Models\WaSendGridAccount::where('user_id', $userId)
            ->where('is_active', true)->get();
        if ($emailAccounts->isNotEmpty()) {
            $accounts['email'] = [];
            foreach ($emailAccounts as $acc) {
                $accounts['email'][] = ['id' => $acc->id, 'name' => $acc->name];
            }
        }

        return $accounts;
    }
}
