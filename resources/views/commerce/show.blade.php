@extends('layouts.app')
@section('title', 'Order ' . $order->order_number . ' — WABot')
@section('content')

@php
$statusMap = [
    'pending' => ['Menunggu','bg-amber-50 text-amber-700'],
    'confirmed' => ['Dikonfirmasi','bg-blue-50 text-blue-700'],
    'paid' => ['Dibayar','bg-emerald-50 text-emerald-700'],
    'shipped' => ['Dikirim','bg-indigo-50 text-indigo-700'],
    'delivered' => ['Selesai','bg-teal-50 text-teal-700'],
    'cancelled' => ['Dibatalkan','bg-red-50 text-red-700'],
];
$st = $statusMap[$order->status] ?? [$order->status,'bg-gray-100 text-gray-600'];
@endphp

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('commerce.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">{{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $order->created_at->format('d M Y H:i') }}</p>
        </div>
    </div>
    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium {{ $st[1] }}">{{ $st[0] }}</span>
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-800">Item Pesanan</div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase"><th class="px-5 py-2">Produk</th><th class="px-5 py-2">Qty</th><th class="px-5 py-2">Harga</th><th class="px-5 py-2 text-right">Subtotal</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($order->items as $it)
                    <tr>
                        <td class="px-5 py-3 font-medium text-gray-800">{{ $it->name }}</td>
                        <td class="px-5 py-3 text-gray-600">{{ $it->qty }}</td>
                        <td class="px-5 py-3 text-gray-600">Rp {{ number_format($it->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-3 text-right font-semibold">Rp {{ number_format($it->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-8 text-center text-gray-400">Tidak ada item</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="border-t border-gray-200"><td colspan="3" class="px-5 py-3 text-right font-semibold text-gray-700">Total</td><td class="px-5 py-3 text-right font-extrabold text-gray-900">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr></tfoot>
            </table>
        </div>

        @if($order->shipping_address || $order->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-sm">
            @if($order->shipping_address)<div class="mb-3"><div class="text-xs font-semibold text-gray-500 mb-1">Alamat Pengiriman</div><p class="text-gray-700">{{ $order->shipping_address }}</p></div>@endif
            @if($order->notes)<div><div class="text-xs font-semibold text-gray-500 mb-1">Catatan</div><p class="text-gray-700">{{ $order->notes }}</p></div>@endif
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="text-xs font-semibold text-gray-500 mb-2">Pelanggan</div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($order->contact?->name ?? 'NA', 0, 2)) }}</div>
                <div>
                    <div class="font-medium text-gray-900">{{ $order->contact?->name ?? '-' }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ preg_replace('/@.*$/', '', $order->contact?->phone ?? '') }}</div>
                </div>
            </div>
            @if($order->payment_method)<div class="mt-3 text-xs text-gray-500">Metode: <span class="font-medium text-gray-700">{{ $order->payment_method }}</span></div>@endif
            @if($order->paid_at)<div class="text-xs text-gray-500">Dibayar: {{ $order->paid_at->format('d M Y H:i') }}</div>@endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
            <div class="text-xs font-semibold text-gray-500 mb-1">Aksi</div>
            @if($order->status === 'pending')
            <form method="POST" action="{{ route('commerce.confirm', $order) }}">@csrf<button class="w-full bg-blue-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-blue-700"><i class="fas fa-check mr-1"></i> Konfirmasi Pesanan</button></form>
            @endif
            @if($order->status === 'confirmed')
            <form method="POST" action="{{ route('commerce.paid', $order) }}" class="space-y-2">@csrf
                <input type="text" name="payment_method" required placeholder="Metode pembayaran" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <input type="url" name="payment_proof_url" placeholder="URL bukti bayar (opsional)" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                <button class="w-full bg-emerald-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-emerald-700"><i class="fas fa-money-bill-wave mr-1"></i> Tandai Dibayar</button>
            </form>
            @endif
            @if($order->status === 'paid')
            <form method="POST" action="{{ route('commerce.ship', $order) }}">@csrf<button class="w-full bg-indigo-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-truck mr-1"></i> Kirim Pesanan</button></form>
            @endif
            @if(!in_array($order->status, ['shipped','delivered','cancelled']))
            <form method="POST" action="{{ route('commerce.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan?')">@csrf<button class="w-full bg-red-50 text-red-700 rounded-xl py-2.5 text-sm font-semibold hover:bg-red-100"><i class="fas fa-times mr-1"></i> Batalkan</button></form>
            @endif
        </div>
    </div>
</div>
@endsection
