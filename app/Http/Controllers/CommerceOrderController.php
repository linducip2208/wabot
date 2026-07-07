<?php

namespace App\Http\Controllers;

use App\Models\WaCommerceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommerceOrderController extends Controller
{
    public function index()
    {
        $orders = WaCommerceOrder::where('user_id', Auth::id())
            ->with('contact')
            ->latest()
            ->get();

        return view('commerce.index', compact('orders'));
    }

    public function show(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $order->load(['contact', 'items', 'session']);

        return view('commerce.show', compact('order'));
    }

    public function confirm(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if ($order->status !== 'pending') {
            return back()->with('error', 'Hanya pesanan pending yang dapat dikonfirmasi.');
        }

        $order->update(['status' => 'confirmed']);

        return back()->with('success', 'Pesanan berhasil dikonfirmasi.');
    }

    public function paid(Request $request, WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (!in_array($order->status, ['confirmed'])) {
            return back()->with('error', 'Pesanan harus dikonfirmasi terlebih dahulu.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|max:100',
            'payment_proof_url' => 'nullable|url|max:1000',
        ]);

        $order->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'payment_proof_url' => $validated['payment_proof_url'] ?? null,
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function ship(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if ($order->status !== 'paid') {
            return back()->with('error', 'Pesanan harus sudah dibayar sebelum dikirim.');
        }

        $order->update(['status' => 'shipped']);

        return back()->with('success', 'Status pesanan diubah menjadi dikirim.');
    }

    public function cancel(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (in_array($order->status, ['shipped', 'delivered'])) {
            return back()->with('error', 'Pesanan yang sudah dikirim tidak dapat dibatalkan.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Pesanan dibatalkan.');
    }
}
