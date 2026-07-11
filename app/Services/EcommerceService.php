<?php

namespace App\Services;

use App\Models\WaStoreIntegration;
use App\Models\WaCommerceOrder;
use App\Models\WaCommerceItem;
use App\Models\WaContact;
use App\Models\WaMessage;
use App\Models\WaMessageTemplate;
use App\Models\WaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommerceService
{
    public function testConnection(WaStoreIntegration $integration): array
    {
        return match ($integration->platform) {
            'woocommerce' => $this->testWooCommerce($integration),
            'shopify' => $this->testShopify($integration),
            default => ['success' => false, 'message' => 'Unsupported platform'],
        };
    }

    public function syncCatalog(WaStoreIntegration $integration, int $limit = 100): array
    {
        return match ($integration->platform) {
            'woocommerce' => $this->syncWooCommerceProducts($integration, $limit),
            'shopify' => $this->syncShopifyProducts($integration, $limit),
            default => ['success' => false, 'message' => 'Unsupported platform'],
        };
    }

    /**
     * Handle incoming webhook from e-commerce platform.
     * Creates a WaCommerceOrder and sends WhatsApp notification.
     */
    public function handleWebhook(WaStoreIntegration $integration, Request $request): array
    {
        $payload = $request->getContent();
        $signature = $request->header('X-WC-Webhook-Signature') ?? $request->header('X-Shopify-Hmac-SHA256') ?? '';

        if (!$this->verifyWebhookSignature($integration, $payload, $signature)) {
            Log::warning('Store webhook signature verification failed', [
                'integration_id' => $integration->id,
                'platform' => $integration->platform,
            ]);
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $body = json_decode($payload, true);
        if (!$body) {
            return ['success' => false, 'message' => 'Invalid JSON payload'];
        }

        return match ($integration->platform) {
            'woocommerce' => $this->processWooCommerceWebhook($integration, $request, $body),
            'shopify' => $this->processShopifyWebhook($integration, $request, $body),
            default => ['success' => false, 'message' => 'Unsupported platform'],
        };
    }

    // ─── WooCommerce ──────────────────────────────────────────

    protected function testWooCommerce(WaStoreIntegration $integration): array
    {
        try {
            $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                ->get(rtrim($integration->base_url, '/') . '/wp-json/wc/v3/orders', ['per_page' => 1]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connected successfully'];
            }

            return ['success' => false, 'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('message') ?? $response->body())];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function syncWooCommerceProducts(WaStoreIntegration $integration, int $limit): array
    {
        try {
            $products = [];
            $page = 1;
            $total = 0;

            do {
                $response = Http::withBasicAuth($integration->api_key, $integration->api_secret)
                    ->get(rtrim($integration->base_url, '/') . '/wp-json/wc/v3/products', [
                        'per_page' => min($limit, 100),
                        'page' => $page,
                    ]);

                if (!$response->successful()) break;

                $items = $response->json();
                if (empty($items)) break;

                foreach ($items as $item) {
                    $products[] = [
                        'external_id' => (string) $item['id'],
                        'name' => $item['name'],
                        'description' => $item['short_description'] ?? $item['description'] ?? '',
                        'price' => (float) $item['price'],
                        'stock' => $item['stock_quantity'] ?? null,
                        'image_url' => $item['images'][0]['src'] ?? null,
                    ];
                }

                $total += count($items);
                $page++;

                if ($total >= $limit) break;
            } while (!empty($items));

            $integration->update([
                'sync_status' => 'synced',
                'last_synced_at' => now(),
            ]);

            return ['success' => true, 'total' => $total, 'products' => $products];
        } catch (\Throwable $e) {
            $integration->update(['sync_status' => 'failed']);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function processWooCommerceWebhook(WaStoreIntegration $integration, Request $request, array $body): array
    {
        $topic = $request->header('X-WC-Webhook-Topic') ?? '';
        $orderData = $body;

        Log::info('WooCommerce webhook received', [
            'topic' => $topic,
            'integration_id' => $integration->id,
        ]);

        if (in_array($topic, ['order.created', 'order.updated'])) {
            return $this->importWooCommerceOrder($integration, $orderData);
        }

        return ['success' => true, 'message' => 'Unhandled topic: ' . $topic];
    }

    // ─── Shopify ─────────────────────────────────────────────

    protected function testShopify(WaStoreIntegration $integration): array
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $integration->api_key,
                'Content-Type' => 'application/json',
            ])->get(rtrim($integration->base_url, '/') . '/admin/api/2024-01/orders.json?limit=1');

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connected successfully'];
            }

            return ['success' => false, 'message' => 'HTTP ' . $response->status() . ': ' . ($response->json('errors') ?? $response->body())];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function syncShopifyProducts(WaStoreIntegration $integration, int $limit): array
    {
        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $integration->api_key,
                'Content-Type' => 'application/json',
            ])->get(rtrim($integration->base_url, '/') . '/admin/api/2024-01/products.json', [
                'limit' => min($limit, 250),
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'HTTP ' . $response->status()];
            }

            $items = $response->json('products', []);
            $products = [];

            foreach ($items as $item) {
                $variant = $item['variants'][0] ?? [];
                $image = $item['images'][0]['src'] ?? null;

                $products[] = [
                    'external_id' => (string) $item['id'],
                    'name' => $item['title'],
                    'description' => $item['body_html'] ?? '',
                    'price' => (float) ($variant['price'] ?? 0),
                    'stock' => $variant['inventory_quantity'] ?? null,
                    'image_url' => $image,
                ];
            }

            $integration->update([
                'sync_status' => 'synced',
                'last_synced_at' => now(),
            ]);

            return ['success' => true, 'total' => count($products), 'products' => $products];
        } catch (\Throwable $e) {
            $integration->update(['sync_status' => 'failed']);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function processShopifyWebhook(WaStoreIntegration $integration, Request $request, array $body): array
    {
        $topic = $request->header('X-Shopify-Topic') ?? '';

        Log::info('Shopify webhook received', [
            'topic' => $topic,
            'integration_id' => $integration->id,
        ]);

        if (in_array($topic, ['orders/create', 'orders/updated'])) {
            return $this->importShopifyOrder($integration, $body);
        }

        return ['success' => true, 'message' => 'Unhandled topic: ' . $topic];
    }

    // ─── Order Import ────────────────────────────────────────

    protected function importWooCommerceOrder(WaStoreIntegration $integration, array $orderData): array
    {
        $billing = $orderData['billing'] ?? [];
        $phone = $billing['phone'] ?? '';
        $name = trim(($billing['first_name'] ?? '') . ' ' . ($billing['last_name'] ?? ''));
        $email = $billing['email'] ?? '';

        $displayPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($displayPhone)) $displayPhone = $phone;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $integration->user_id, 'phone' => $phone],
            [
                'name' => $name ?: $displayPhone,
                'display_phone' => $displayPhone,
            ]
        );

        $total = (float) ($orderData['total'] ?? 0);
        $status = $orderData['status'] ?? 'pending';
        $mappedStatus = match ($status) {
            'processing' => 'confirmed',
            'completed' => 'paid',
            'on-hold' => 'pending',
            'cancelled', 'refunded', 'failed' => 'cancelled',
            default => 'pending',
        };

        $existingOrder = WaCommerceOrder::where('user_id', $integration->user_id)
            ->where('order_number', 'WC-' . ($orderData['id'] ?? ''))
            ->first();

        if ($existingOrder) {
            $existingOrder->update([
                'status' => $mappedStatus,
                'total' => $total,
            ]);
            $order = $existingOrder;
        } else {
            $order = WaCommerceOrder::create([
                'user_id' => $integration->user_id,
                'contact_id' => $contact->id,
                'order_number' => 'WC-' . ($orderData['id'] ?? ''),
                'total' => $total,
                'status' => $mappedStatus,
                'notes' => $orderData['customer_note'] ?? null,
                'shipping_address' => $email,
            ]);

            foreach ($orderData['line_items'] ?? [] as $item) {
                WaCommerceItem::create([
                    'order_id' => $order->id,
                    'name' => $item['name'],
                    'qty' => (int) ($item['quantity'] ?? 1),
                    'price' => (float) ($item['price'] ?? 0),
                    'subtotal' => (float) ($item['total'] ?? 0),
                ]);
            }
        }

        $this->sendOrderNotification($integration, $contact, $order, $mappedStatus);

        return ['success' => true, 'order_number' => $order->order_number];
    }

    protected function importShopifyOrder(WaStoreIntegration $integration, array $orderData): array
    {
        $shipping = $orderData['shipping_address'] ?? [];
        $customer = $orderData['customer'] ?? [];
        $phone = $shipping['phone'] ?? $customer['phone'] ?? ($orderData['billing_address']['phone'] ?? '');
        $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        $email = $customer['email'] ?? ($orderData['email'] ?? '');

        $displayPhone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($displayPhone)) $displayPhone = $phone;

        $contact = WaContact::firstOrCreate(
            ['user_id' => $integration->user_id, 'phone' => $phone],
            [
                'name' => $name ?: $displayPhone,
                'display_phone' => $displayPhone,
            ]
        );

        $total = (float) ($orderData['total_price'] ?? 0);
        $financialStatus = $orderData['financial_status'] ?? 'pending';
        $mappedStatus = match ($financialStatus) {
            'paid', 'partially_paid' => 'paid',
            'pending' => 'pending',
            'voided', 'refunded' => 'cancelled',
            default => 'pending',
        };

        $existingOrder = WaCommerceOrder::where('user_id', $integration->user_id)
            ->where('order_number', 'SH-' . ($orderData['id'] ?? ''))
            ->first();

        if ($existingOrder) {
            $existingOrder->update([
                'status' => $mappedStatus,
                'total' => $total,
            ]);
            $order = $existingOrder;
        } else {
            $order = WaCommerceOrder::create([
                'user_id' => $integration->user_id,
                'contact_id' => $contact->id,
                'order_number' => 'SH-' . ($orderData['id'] ?? ''),
                'total' => $total,
                'status' => $mappedStatus,
                'shipping_address' => $email,
            ]);

            foreach ($orderData['line_items'] ?? [] as $item) {
                WaCommerceItem::create([
                    'order_id' => $order->id,
                    'name' => $item['name'] ?? $item['title'] ?? '',
                    'qty' => (int) ($item['quantity'] ?? 1),
                    'price' => (float) ($item['price'] ?? 0),
                    'subtotal' => (float) (($item['price'] ?? 0) * ($item['quantity'] ?? 1)),
                ]);
            }
        }

        $this->sendOrderNotification($integration, $contact, $order, $mappedStatus);

        return ['success' => true, 'order_number' => $order->order_number];
    }

    // ─── WhatsApp Notification ───────────────────────────────

    protected function sendOrderNotification(WaStoreIntegration $integration, WaContact $contact, WaCommerceOrder $order, string $status): void
    {
        $sessions = WaSession::where('user_id', $integration->user_id)
            ->where('is_active', true)
            ->where('status', 'connected')
            ->get();

        $session = $sessions->first();
        if (!$session) {
            Log::warning('No active session for order notification', ['user_id' => $integration->user_id]);
            return;
        }

        $settings = $integration->settings ?? [];
        $message = $this->buildOrderMessage($order, $status, $settings);

        // Try Meta Cloud API first
        if ($session->meta_account_id) {
            $metaAccount = \App\Models\WaMetaAccount::find($session->meta_account_id);
            if ($metaAccount) {
                try {
                    app(MetaApiService::class)->sendText($metaAccount, $contact->phone, $message);
                } catch (\Throwable $e) {
                    Log::error('Meta notification failed: ' . $e->getMessage());
                }
            }
        }

        WaMessage::create([
            'user_id' => $integration->user_id,
            'session_id' => $session->id,
            'contact_id' => $contact->id,
            'direction' => 'out',
            'type' => 'text',
            'channel' => $session->meta_account_id ? 'meta' : 'baileys',
            'message' => $message,
            'phone' => $contact->phone,
            'status' => 'sent',
        ]);

        Log::info('Order notification sent', [
            'order' => $order->order_number,
            'phone' => $contact->phone,
            'status' => $status,
        ]);
    }

    protected function buildOrderMessage(WaCommerceOrder $order, string $status, array $settings): string
    {
        $statusEmoji = match ($status) {
            'confirmed' => '✅',
            'paid' => '💰',
            'shipped' => '🚚',
            'cancelled' => '❌',
            default => '🛒',
        };

        $statusLabel = match ($status) {
            'confirmed' => 'Dikonfirmasi',
            'paid' => 'Sudah Dibayar',
            'shipped' => 'Dikirim',
            'cancelled' => 'Dibatalkan',
            'pending' => 'Menunggu',
            default => $status,
        };

        $text = "{$statusEmoji} *Pesanan #{$order->order_number}*\n\n";
        $text .= "Status: *{$statusLabel}*\n\n";

        $text .= "*Rincian Pesanan:*\n";
        foreach ($order->items as $item) {
            $text .= "  • {$item->name} x{$item->qty} — Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $text .= "\n*Total: Rp " . number_format($order->total, 0, ',', '.') . "*";

        if ($status === 'paid') {
            $text .= "\n\n_Pembayaran telah kami terima. Pesanan Anda akan segera diproses._";
        } elseif ($status === 'shipped') {
            $text .= "\n\n_Pesanan Anda telah dikirim. Terima kasih telah berbelanja!_";
        }

        return $text;
    }

    // ─── Webhook Signature Verification ──────────────────────

    protected function verifyWebhookSignature(WaStoreIntegration $integration, string $payload, string $signature): bool
    {
        $secret = $integration->webhook_secret;
        if (empty($secret)) return true; // skip if no secret set

        if ($integration->platform === 'shopify') {
            $computed = base64_encode(hash_hmac('sha256', $payload, $secret, true));
            return hash_equals($computed, $signature);
        }

        // WooCommerce uses HMAC-SHA256 as hex
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }
}
