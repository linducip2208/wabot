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
            return back()->with('error', __('messages.error.only_pending_confirmable'));
        }

        $order->update(['status' => 'confirmed']);

        return back()->with('success', __('messages.success.order_confirmed'));
    }

    public function paid(Request $request, WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (!in_array($order->status, ['confirmed'])) {
            return back()->with('error', __('messages.error.order_must_be_confirmed'));
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

        return back()->with('success', __('messages.success.payment_recorded'));
    }

    public function ship(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if ($order->status !== 'paid') {
            return back()->with('error', __('messages.error.order_must_be_paid'));
        }

        $order->update(['status' => 'shipped']);

        return back()->with('success', __('messages.success.order_shipped'));
    }

    public function cancel(WaCommerceOrder $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (in_array($order->status, ['shipped', 'delivered'])) {
            return back()->with('error', __('messages.error.shipped_cannot_cancel'));
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', __('messages.success.order_cancelled'));
    }
}
