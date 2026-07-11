<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected ?string $keyId = null;
    protected ?string $keySecret = null;

    public function __construct(?PaymentGateway $gateway = null)
    {
        if ($gateway && $gateway->driver === 'razorpay') {
            $this->keyId = $gateway->api_key;
            $this->keySecret = $gateway->api_secret;
        }
    }

    public function createOrder(float $amount, string $currency = 'INR', array $notes = []): ?array
    {
        if (!$this->keyId || !$this->keySecret) return null;

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => (int) ($amount * 100),
                    'currency' => strtoupper($currency),
                    'receipt' => $notes['receipt'] ?? ('rcpt_' . uniqid()),
                    'notes' => $notes,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Razorpay order creation failed', ['response' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Razorpay order error: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        if (!$this->keySecret) return false;

        $payload = $orderId . '|' . $paymentId;
        $expected = hash_hmac('sha256', $payload, $this->keySecret);

        return hash_equals($expected, $signature);
    }

    public function handleWebhook(Request $request, PaymentGateway $gateway): array
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature') ?? '';
        $webhookSecret = $gateway->webhook_secret ?? $gateway->api_secret;

        if ($webhookSecret) {
            $expected = hash_hmac('sha256', $payload, $webhookSecret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('Razorpay webhook signature invalid', ['gateway_id' => $gateway->id]);
                return ['success' => false, 'message' => 'Invalid signature'];
            }
        }

        $event = json_decode($payload, true);
        $type = $event['event'] ?? '';
        $data = $event['payload']['payment']['entity'] ?? ($event['payload']['order']['entity'] ?? []);

        Log::info('Razorpay webhook received', ['event' => $type]);

        return match ($type) {
            'payment.captured' => $this->handlePaymentCaptured($data),
            'order.paid' => $this->handleOrderPaid($data),
            default => ['success' => true, 'message' => "Unhandled: {$type}"],
        };
    }

    protected function handlePaymentCaptured(array $payment): array
    {
        $notes = $payment['notes'] ?? [];
        $transactionId = $notes['transaction_id'] ?? null;

        if ($transactionId) {
            $transaction = PaymentTransaction::find($transactionId);
            if ($transaction && $transaction->status !== 'completed') {
                $transaction->update([
                    'status' => 'completed',
                    'gateway_meta' => array_merge($transaction->gateway_meta ?? [], [
                        'razorpay_payment_id' => $payment['id'],
                        'razorpay_order_id' => $payment['order_id'] ?? null,
                        'razorpay_method' => $payment['method'] ?? null,
                    ]),
                ]);
            }
        }

        return ['success' => true, 'razorpay_payment_id' => $payment['id']];
    }

    protected function handleOrderPaid(array $order): array
    {
        Log::info('Razorpay order.paid', ['id' => $order['id']]);
        return ['success' => true, 'razorpay_order_id' => $order['id']];
    }
}
