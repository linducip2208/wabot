<?php

namespace App\Http\Controllers;

use App\Models\WaStoreIntegration;
use App\Services\EcommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function __construct(
        protected EcommerceService $ecommerce,
    ) {}

    public function index()
    {
        $integrations = WaStoreIntegration::where('user_id', Auth::id())->latest()->get();
        return view('store.index', compact('integrations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|in:woocommerce,shopify',
            'name' => 'required|string|max:255',
            'base_url' => 'required|url|max:500',
            'api_key' => 'required|string|max:500',
            'api_secret' => 'required|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
        ]);

        WaStoreIntegration::create([
            'user_id' => Auth::id(),
            'platform' => $validated['platform'],
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'api_key' => $validated['api_key'],
            'api_secret' => $validated['api_secret'],
            'webhook_secret' => $validated['webhook_secret'] ?? null,
            'is_active' => true,
            'sync_status' => 'never',
        ]);

        return back()->with('success', __('messages.success.store_integration_added'));
    }

    public function connect(WaStoreIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $result = $this->ecommerce->testConnection($integration);

        if ($result['success']) {
            $integration->update(['is_active' => true]);
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function sync(WaStoreIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $integration->update(['sync_status' => 'syncing']);

        $result = $this->ecommerce->syncCatalog($integration);

        if ($result['success']) {
            return back()->with('success', __('messages.success.store_synced', ['count' => $result['total'] ?? 0]));
        }

        return back()->with('error', $result['message'] ?? __('messages.error.store_sync_failed'));
    }

    public function webhook(WaStoreIntegration $integration, Request $request)
    {
        $result = $this->ecommerce->handleWebhook($integration, $request);

        return response()->json($result, $result['success'] ? 200 : 401);
    }

    public function update(Request $request, WaStoreIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_url' => 'required|url|max:500',
            'api_key' => 'nullable|string|max:500',
            'api_secret' => 'nullable|string|max:500',
            'webhook_secret' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['api_key'])) {
            $integration->api_key = $validated['api_key'];
        }
        if (!empty($validated['api_secret'])) {
            $integration->api_secret = $validated['api_secret'];
        }
        if (isset($validated['webhook_secret'])) {
            $integration->webhook_secret = $validated['webhook_secret'] ?: null;
        }

        $integration->update([
            'name' => $validated['name'],
            'base_url' => rtrim($validated['base_url'], '/'),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return back()->with('success', __('messages.success.store_integration_updated'));
    }

    public function updateSettings(Request $request, WaStoreIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'order_template_id' => 'nullable|integer|exists:wa_message_templates,id',
            'tracking_template_id' => 'nullable|integer|exists:wa_message_templates,id',
            'auto_reply_keywords' => 'nullable|string|max:1000',
        ]);

        $settings = $integration->settings ?? [];
        $settings['order_template_id'] = $validated['order_template_id'] ?? null;
        $settings['tracking_template_id'] = $validated['tracking_template_id'] ?? null;
        $settings['auto_reply_keywords'] = $validated['auto_reply_keywords'] ?? null;

        $integration->update(['settings' => $settings]);

        return back()->with('success', __('messages.success.settings_saved'));
    }

    public function destroy(WaStoreIntegration $integration)
    {
        abort_if($integration->user_id !== Auth::id(), 403);

        $integration->delete();

        return back()->with('success', __('messages.success.store_integration_deleted'));
    }
}
