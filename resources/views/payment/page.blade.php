@extends('layouts.app')
@section('title', __('common.payment') . ' — WABot')
@section('content')

<div class="max-w-2xl mx-auto">
    <a href="{{ route('plans.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; {{ __('common.back') }}</a>
    <h1 class="text-2xl font-extrabold text-gray-900 mt-1 mb-2">{{ __('common.payment') }}</h1>
    <p class="text-gray-500 mb-6">{{ __('common.plan') }} {{ $plan->name }} — Rp {{ number_format($plan->price, 0, ',', '.') }}</p>

    <div x-data="paymentForm()" class="space-y-5">
        {{-- Gateway Selection --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-3">{{ __('common.select') }} Metode {{ __('common.payment') }}</h2>
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

        {{-- Instructions — dynamic by gateway --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            <h2 class="font-bold text-gray-900 mb-3">Instruksi {{ __('common.payment') }}</h2>
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-gray-200">
                    <div class="w-6 h-6 rounded flex items-center justify-center text-white text-[10px] font-bold" x-bind:style="'background: ' + selectedColor"></div>
                    <span class="font-semibold text-sm" x-text="selectedName"></span>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-line leading-relaxed" x-text="selectedInstructions || '{{ __('common.select') }} metode {{ __('common.payment') }} terlebih dahulu.'"></div>
            </div>
        </div>

        {{-- Upload Payment --}}
        <form method="POST" action="{{ route('payment.upload', $subscription) }}" class="bg-white rounded-xl border border-gray-200 p-5" x-show="gatewayId" x-transition>
            @csrf
            <input type="hidden" name="gateway_id" x-model="gatewayId">
            <input type="hidden" name="amount" value="{{ $plan->price }}">

            <h2 class="font-bold text-gray-900 mb-3">Konfirmasi {{ __('common.payment') }}</h2>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 mb-4">
                <i class="fas fa-info-circle mr-1"></i> Setelah transfer, klik "Konfirmasi {{ __('common.payment') }}". Admin akan verifikasi.
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl mb-4">
                <span class="text-sm text-gray-500">{{ __('common.total') }} {{ __('common.payment') }}</span>
                <span class="text-xl font-extrabold text-gray-900">Rp {{ number_format($plan->price, 0, ',', '.') }}</span>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">
                <i class="fas fa-check-circle mr-1"></i> Konfirmasi {{ __('common.payment') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const gatewayData = {!! json_encode($gateways->mapWithKeys(fn($g) => [$g->id => ['name' => $g->name, 'color' => $g->logo_color, 'instructions' => $g->instructions]])) !!};

document.addEventListener('alpine:init', () => {
    Alpine.data('paymentForm', () => ({
        gatewayId: null,
        selectedName: '',
        selectedColor: '#3b82f6',
        selectedInstructions: '',

        selectGateway(id) {
            this.gatewayId = id;
            const g = gatewayData[id];
            this.selectedName = g.name;
            this.selectedColor = g.color || '#3b82f6';
            this.selectedInstructions = g.instructions || '';
        }
    }));
});
</script>
@endpush
@endsection
