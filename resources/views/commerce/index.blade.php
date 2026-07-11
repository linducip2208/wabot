@extends('layouts.app')
@section('title', 'Orders — WABot')
@section('content')

@php
$statusMap = [
    'pending' => ['common.pending','bg-amber-50 text-amber-700','fa-clock'],
    'confirmed' => ['commerce.confirmed','bg-blue-50 text-blue-700','fa-check'],
    'paid' => ['commerce.paid','bg-emerald-50 text-emerald-700','fa-money-bill-wave'],
    'shipped' => ['commerce.shipped','bg-indigo-50 text-indigo-700','fa-truck'],
    'delivered' => ['common.completed','bg-teal-50 text-teal-700','fa-box-open'],
    'cancelled' => ['common.cancelled','bg-red-50 text-red-700','fa-times-circle'],
];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Orders</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('commerce.subtitle', ['count' => $orders->count()]) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">No. Order</th>
                <th class="px-5 py-3">{{ __('common.customer') }}</th>
                <th class="px-5 py-3">{{ __('common.total') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('common.date') }}</th>
                <th class="px-5 py-3 w-16 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($orders as $o)
            @php $st = $statusMap[$o->status] ?? [$o->status,'bg-gray-100 text-gray-600','fa-circle']; @endphp
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-800">{{ $o->order_number }}</td>
                <td class="px-5 py-3">{{ $o->contact?->name ?? '-' }}</td>
                <td class="px-5 py-3 font-semibold text-gray-800">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td class="px-5 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $st[1] }}"><i class="fas {{ $st[2] }} text-[9px]"></i> {{ __($st[0]) }}</span></td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400">{{ $o->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('commerce.show', $o) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-eye text-xs"></i></a>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-shopping-cart text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium">{{ __('commerce.no_orders') }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ __('commerce.no_orders_hint') }}</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
