@extends('layouts.app')
@section('title', 'Langganan Saya — WABot')
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">{{ __('subscriptions.my_subscriptions') }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('subscriptions.subtitle') }}</p>
</div>

{{-- Current Plan --}}
@if($currentPlan)
<div class="mb-6 bg-gradient-to-r from-brand-50 to-blue-50 border border-brand-200 rounded-2xl px-5 py-4">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-crown text-brand-600"></i></div>
            <div>
                <span class="text-sm text-brand-800">{{ __('common.plan') }} {{ __('common.active') }}: <strong class="text-base">{{ $currentPlan->name }}</strong></span>
                @if($currentSubscription)
                <div class="text-xs text-brand-600 mt-0.5">
                    @if($currentSubscription->ends_at)
                        <span><i class="fas fa-calendar-alt mr-1"></i> {{ __('subscriptions.valid_until') }} {{ $currentSubscription->ends_at->format('d M Y') }}</span>
                    @else
                        <span><i class="fas fa-calendar-alt mr-1"></i> {{ __('subscriptions.active_forever') }}</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        <span class="text-xs bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full font-medium">{{ __('common.active') }}</span>
    </div>
</div>
@else
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500"></i>
    <div>
        <span class="text-sm text-amber-800 font-medium">{{ __('subscriptions.no_active_plan') }}</span>
        <a href="{{ route('plans.index') }}" class="text-sm text-brand-600 font-semibold hover:underline ml-1">{{ __('subscriptions.select_plan_now') }}</a>
    </div>
</div>
@endif

{{-- Usage Limits --}}
<div class="grid md:grid-cols-3 gap-3 mb-6">
    @foreach([
        ['fas fa-mobile-alt', __('common.session') . ' WhatsApp', $usage['sessions']['current'], $usage['sessions']['limit'], 'bg-sky-50 text-sky-500'],
        ['fas fa-address-book', __('common.contact'), $usage['contacts']['current'], $usage['contacts']['limit'], 'bg-violet-50 text-violet-500'],
        ['fas fa-robot', 'Auto-Reply', $usage['autoreplies']['current'], $usage['autoreplies']['limit'], 'bg-emerald-50 text-emerald-500'],
    ] as [$icon, $label, $current, $limit, $colorClass])
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg {{ $colorClass }} flex items-center justify-center"><i class="{{ $icon }} text-sm"></i></div>
                <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
            </div>
            <span class="text-xs font-mono {{ $limit > 0 && $current >= $limit ? 'text-red-500' : 'text-gray-400' }}">
                {{ number_format($current) }} / {{ $limit > 0 ? number_format($limit) : '∞' }}
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all
                {{ $limit > 0 ? ($current >= $limit ? 'bg-red-400' : 'bg-emerald-400') : 'bg-brand-400' }}"
                style="width: {{ $limit > 0 ? min(($current / max($limit, 1)) * 100, 100) : 100 }}%">
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Voucher Redeem --}}
<div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 card-lift">
    <h2 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="fas fa-ticket-alt text-brand-500"></i> {{ __('subscriptions.redeem_voucher') }}</h2>
    <p class="text-sm text-gray-500 mb-4">{{ __('subscriptions.redeem_hint') }}</p>
    <form method="POST" action="{{ route('vouchers.redeem') }}" class="flex items-center gap-3 max-w-lg">
        @csrf
        <input type="text" name="code" placeholder="{{ __('subscriptions.enter_code') }}" required
            class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-mono uppercase placeholder:normal-case placeholder:font-sans focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
            style="letter-spacing: 0.15em;">
        <button type="submit" class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2 flex-shrink-0">
            <i class="fas fa-check text-xs"></i> {{ __('subscriptions.redeem') }}
        </button>
    </form>
</div>

{{-- Subscription History --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-history text-brand-500"></i> {{ __('subscriptions.history_title') }}</h2>
        <span class="text-xs text-gray-400">{{ __('subscriptions.count_records', ['count' => $subscriptions->count()]) }}</span>
    </div>
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.plan') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('subscriptions.starts') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('subscriptions.ends') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($subscriptions as $sub)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-brand-50 flex items-center justify-center"><i class="fas fa-box text-brand-400 text-xs"></i></div>
                        <span class="font-medium text-gray-900 text-xs">{{ $sub->plan?->name ?? __('common.none') }}</span>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-600">{{ $sub->starts_at?->format('d M Y H:i') ?? '-' }}</td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-600">{{ $sub->ends_at?->format('d M Y H:i') ?? '-' }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $sub->status === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $sub->status === 'expired' ? 'bg-gray-100 text-gray-600' : '' }}
                        {{ $sub->status === 'inactive' ? 'bg-amber-50 text-amber-700' : '' }}">
                        {{ $sub->status === 'active' ? __('common.active') : ($sub->status === 'expired' ? __('common.expired') : ucfirst($sub->status)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-12 text-center">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-history text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 text-sm">{{ __('subscriptions.no_history') }}</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
