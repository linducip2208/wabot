@extends('layouts.app')
@section('title', __('credits.payment_title'))
@section('content')

<div class="max-w-2xl mx-auto">
    <a href="{{ route('credits.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('common.back') }}</a>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-2">{{ __('credits.payment_title') }}</h1>
    <p class="text-gray-500 mb-6">{{ $pack->name }} — {{ number_format($pack->credits) }} {{ __('credits.credits') }} — Rp {{ number_format($pack->price, 0, ',', '.') }}</p>

    <div x-data="paymentForm()" class="space-y-5">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __('common.select') }} {{ __('common.payment_method') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($gateways as $g)
                <button type="button" @click="selectGateway({{ $g->id }})"
                    class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition text-center"
                    :class="gatewayId === {{ $g->id }} ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background: {{ $g->logo_color ?? '#3b82f6' }}">
                        {{ substr($g->name, 0, 2) }}
                    </div>
                    <span class="text-xs font-medium text-gray-700">{{ $g->name }}</span>
                </button>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('credits.callback', $payment) }}" class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            @csrf
            <input type="hidden" name="gateway_id" x-model="gatewayId">

            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl mb-4">
                <span class="text-sm text-gray-500">{{ __('common.total') }}</span>
                <span class="text-xl font-extrabold text-gray-900">Rp {{ number_format($pack->price, 0, ',', '.') }}</span>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
                <i class="fas fa-check-circle mr-1"></i> {{ __('credits.confirm_payment') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const gatewayData = @json($gateways->mapWithKeys(fn($g) => [$g->id => ['name' => $g->name, 'color' => $g->logo_color]]));

document.addEventListener('alpine:init', () => {
    Alpine.data('paymentForm', () => ({
        gatewayId: null,
        selectGateway(id) { this.gatewayId = id; }
    }));
});
</script>
@endpush
@endsection
