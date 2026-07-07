<?php

namespace App\Services;

use App\Models\WaCommerceOrder;
use App\Models\WaCommerceItem;
use App\Models\WaCatalogItem;
use Illuminate\Support\Str;

class CommerceService
{
    public function createOrder(int $userId, int $contactId, array $items, ?int $sessionId = null): WaCommerceOrder
    {
        $total = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $catalogItem = WaCatalogItem::findOrFail($item['catalog_item_id']);
            $qty = $item['qty'] ?? 1;
            $subtotal = $catalogItem->price * $qty;
            $total += $subtotal;

            $orderItems[] = [
                'catalog_item_id' => $catalogItem->id,
                'name' => $catalogItem->name,
                'qty' => $qty,
                'price' => $catalogItem->price,
                'subtotal' => $subtotal,
            ];
        }

        $order = WaCommerceOrder::create([
            'user_id' => $userId,
            'contact_id' => $contactId,
            'session_id' => $sessionId,
            'order_number' => 'ORD-' . date('Ymd') . '-' . Str::upper(Str::random(5)),
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($orderItems as $oi) {
            $order->items()->create($oi);
        }

        return $order;
    }

    public function confirmPayment(WaCommerceOrder $order, string $paymentMethod, ?string $proofUrl = null): void
    {
        $order->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_proof_url' => $proofUrl,
            'paid_at' => now(),
        ]);
    }

    public function generateOrderSummary(WaCommerceOrder $order): string
    {
        $text = "🧾 *Pesanan #{$order->order_number}*\n";
        $text .= "Status: " . strtoupper($order->status) . "\n\n";
        $text .= "*Item:*\n";

        foreach ($order->items as $item) {
            $text .= "  {$item->name} x{$item->qty} — Rp " . number_format($item->subtotal, 0, ',', '.') . "\n";
        }

        $text .= "\n*Total: Rp " . number_format($order->total, 0, ',', '.') . "*";
        return $text;
    }

    public function getStats(int $userId): array
    {
        $today = WaCommerceOrder::where('user_id', $userId)->whereDate('created_at', today());
        $month = WaCommerceOrder::where('user_id', $userId)->whereMonth('created_at', now()->month);

        return [
            'today_orders' => $today->count(),
            'today_revenue' => $today->where('status', '!=', 'cancelled')->sum('total'),
            'month_orders' => $month->count(),
            'month_revenue' => $month->where('status', '!=', 'cancelled')->sum('total'),
            'pending' => WaCommerceOrder::where('user_id', $userId)->where('status', 'pending')->count(),
        ];
    }
}
