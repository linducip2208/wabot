@extends('layouts.app')
@section('title', 'Paket — WABot')
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">Paket Langganan</h1>
    <p class="text-sm text-gray-500 mt-0.5">Pilih paket sesuai kebutuhan bisnis Anda</p>
</div>

@if($currentPlan)
<div class="mb-6 bg-gradient-to-r from-brand-50 to-blue-50 border border-brand-200 rounded-2xl px-5 py-4 flex items-center justify-between flex-wrap gap-3">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-crown text-brand-600"></i></div>
        <div>
            <span class="text-sm text-brand-800">Paket aktif: <strong class="text-base">{{ $currentPlan->name }}</strong></span>
            <div class="text-xs text-brand-600 mt-0.5">
                <span><i class="fas fa-mobile-alt mr-1"></i> {{ $currentPlan->max_sessions }} sesi</span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-address-book mr-1"></i> {{ number_format($currentPlan->max_contacts) }} kontak</span>
                <span class="mx-2">·</span>
                <span><i class="fas fa-robot mr-1"></i> {{ $currentPlan->max_autoreplies }} auto-reply</span>
            </div>
        </div>
    </div>
    <span class="text-xs bg-brand-100 text-brand-700 px-3 py-1.5 rounded-full font-medium">Aktif</span>
</div>
@endif

<div class="grid md:grid-cols-3 gap-5">
    @foreach($plans as $plan)
    @php
        $isActive = $currentPlan && $currentPlan->id === $plan->id;
        $colors = [
            'free' => ['bg-gradient-to-b from-gray-50 to-white', 'bg-gray-100 text-gray-600', 'text-gray-400'],
            'growth' => ['bg-gradient-to-b from-brand-50 to-white', 'bg-brand-100 text-brand-600', 'text-brand-300'],
            'enterprise' => ['bg-gradient-to-b from-violet-50 to-white', 'bg-violet-100 text-violet-600', 'text-violet-300'],
        ][$plan->slug] ?? ['', '', ''];
    @endphp
    <div class="bg-white rounded-2xl border {{ $isActive ? 'border-brand-400 ring-2 ring-brand-100 shadow-lg' : 'border-gray-200' }} overflow-hidden card-lift flex flex-col relative">
        @if($isActive)
        <div class="absolute top-3 right-3 bg-brand-500 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full">Aktif</div>
        @endif

        <div class="p-6 {{ $colors[0] }}">
            <div class="w-12 h-12 rounded-xl {{ $colors[1] }} flex items-center justify-center mb-3">
                <i class="fas {{ $plan->slug === 'free' ? 'fa-gift' : ($plan->slug === 'growth' ? 'fa-rocket' : 'fa-building') }} text-xl {{ $colors[2] }}"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">{{ $plan->name }}</h2>
            <div class="mt-2">
                <span class="text-3xl font-extrabold text-gray-900">
                    @if($plan->price > 0) Rp {{ number_format($plan->price, 0, ',', '.') }} @else Gratis @endif
                </span>
                @if($plan->price > 0)
                <span class="text-sm text-gray-500">/ bln</span>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-2.5 flex-1">
            @foreach([
                ['fas fa-mobile-alt', $plan->max_sessions . ' Sesi WhatsApp'],
                ['fas fa-address-book', number_format($plan->max_contacts) . ' Kontak'],
                ['fas fa-robot', $plan->max_autoreplies . ' Auto-Reply Rules'],
                ['fas fa-bullhorn', number_format($plan->max_campaign_recipients) . ' Penerima/Kampanye'],
            ] as [$icon, $label])
            <div class="flex items-center gap-2.5 text-sm">
                <div class="w-5 h-5 rounded-full bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check text-[10px] text-emerald-500"></i>
                </div>
                <div class="flex items-center gap-2 text-gray-700"><i class="{{ $icon }} w-4 text-center text-gray-400"></i> {{ $label }}</div>
            </div>
            @endforeach

            @if($plan->features)
                <div class="pt-2 mt-2 border-t border-gray-100"></div>
                @foreach($plan->features as $feat)
                <div class="flex items-center gap-2.5 text-sm">
                    <div class="w-5 h-5 rounded-full bg-brand-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-check text-[10px] text-brand-500"></i>
                    </div>
                    <span class="text-gray-600">{{ $feat }}</span>
                </div>
                @endforeach
            @endif
        </div>

        <div class="p-6 pt-0">
            @if($isActive)
            <button disabled class="w-full bg-gray-100 text-gray-500 rounded-xl py-3 font-semibold text-sm cursor-not-allowed flex items-center justify-center gap-2">
                <i class="fas fa-check-circle"></i> Paket Aktif
            </button>
            @else
            <form method="POST" action="{{ route('plans.subscribe', $plan) }}">
                @csrf
                <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition shadow-sm flex items-center justify-center gap-2">
                    {{ $plan->price > 0 ? 'Pilih Paket' : 'Aktifkan Gratis' }} <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection
