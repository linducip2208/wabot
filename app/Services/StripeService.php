<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected ?string $secretKey = null;

    public function __construct(?PaymentGateway $gateway = null)
    {
        if ($gateway && $gateway->driver === 'stripe') {
            $this->secretKey = $gateway->api_key;
        }
    }

    public function createCheckoutSession(float $amount, string $currency, string $successUrl, string $cancelUrl, array $metadata = []): ?array
    {
        if (!$this->secretKey) return null;

        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->asForm()
                ->post('https://api.stripe.com/v1/checkout/sessions', [
                    'payment_method_types[]' => 'card',
                    'line_items[0][price_data][currency]' => strtolower($currency),
                    'line_items[0][price_data][product_data][name]' => $metadata['description'] ?? 'Payment',
                    'line_items[0][price_data][unit_amount]' => (int) ($amount * 100),
                    'line_items[0][quantity]' => 1,
                    'mode' => 'payment',
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'metadata' => $metadata,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Stripe checkout session failed', ['response' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Stripe checkout error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyWebhook(string $payload, string $signature, string $webhookSecret): bool
    {
        if (empty($webhookSecret)) return false;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);
            return $event !== null;
        } catch (\Throwable $e) {
            Log::error('Stripe webhook verification failed: ' . $e->getMessage());
            return false;
        }
    }

    public function handleWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature') ?? '';
        $webhookSecret = $gateway->api_secret;

        if (!$this->verifyWebhook($payload, $signature, $webhookSecret)) {
            Log::warning('Stripe webhook signature invalid', ['gateway_id' => $gateway->id]);
            return ['success' => false, 'message' => 'Invalid signature'];
        }

        $event = json_decode($payload, true);
        $type = $event['type'] ?? '';
        $data = $event['data']['object'] ?? [];

        Log::info('Stripe webhook received', ['type' => $type]);

        return match ($type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($data),
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($data),
            default => ['success' => true, 'message' => "Unhandled: {$type}"],
        };
    }

    protected function handleCheckoutCompleted(array $session): array
    {
        $metadata = $session['metadata'] ?? [];
        $transactionId = $metadata['transaction_id'] ?? null;

        if ($transactionId) {
            $transaction = PaymentTransaction::find($transactionId);
            if ($transaction && $transaction->status !== 'completed') {
                $transaction->update([
                    'status' => 'completed',
                    'gateway_meta' => array_merge($transaction->gateway_meta ?? [], [
                        'stripe_checkout_id' => $session['id'],
                        'stripe_payment_intent' => $session['payment_intent'] ?? null,
                    ]),
                ]);
            }
        }

        return ['success' => true, 'stripe_checkout_id' => $session['id']];
    }

    protected function handlePaymentSucceeded(array $intent): array
    {
        Log::info('Stripe payment_intent.succeeded', ['id' => $intent['id']]);
        return ['success' => true, 'payment_intent_id' => $intent['id']];
    }
}
