@extends('layouts.app')
@section('title', __('credits.page_title'))
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">{{ __('credits.page_title') }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('credits.subtitle') }}</p>
</div>

{{-- Balance Card --}}
<div class="grid lg:grid-cols-3 gap-5 mb-5">
    <div class="bg-gradient-to-br from-brand-600 to-brand-800 rounded-2xl p-6 text-white shadow-lg lg:col-span-1">
        <div class="text-sm text-brand-200 mb-1">{{ __('credits.current_balance') }}</div>
        <div class="text-4xl font-extrabold tracking-tight">{{ number_format($balance) }}</div>
        <div class="text-brand-300 text-sm mt-1">{{ __('credits.credits') }}</div>
        <div class="mt-4 flex items-center gap-2 text-xs text-brand-200">
            <i class="fas fa-info-circle"></i> {{ __('credits.one_credit_per_ai_call') }}
        </div>
    </div>

    <div class="lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">{{ __('credits.buy_credits') }}</h2>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @forelse($packs as $pack)
            <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex flex-col">
                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $pack->name }}</div>
                <div class="text-2xl font-extrabold text-gray-900">{{ number_format($pack->credits) }} <span class="text-sm font-medium text-gray-400">{{ __('credits.credits') }}</span></div>
                <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                    <span class="text-lg font-bold text-brand-600">Rp {{ number_format($pack->price, 0, ',', '.') }}</span>
                    <form method="POST" action="{{ route('credits.purchase') }}">
                        @csrf
                        <input type="hidden" name="pack_id" value="{{ $pack->id }}">
                        <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-brand-700 transition">
                            {{ __('credits.buy') }}
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-coins text-xl text-gray-400"></i>
                </div>
                <p class="text-gray-500 font-medium">{{ __('credits.no_packs') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Transaction History --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-700">{{ __('credits.transaction_history') }}</h2>
        <span class="text-xs text-gray-400">{{ trans_choice('credits.count', $transactions->count()) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">{{ __('credits.type') }}</th>
                    <th class="px-5 py-3">{{ __('credits.amount') }}</th>
                    <th class="px-5 py-3">{{ __('credits.balance') }}</th>
                    <th class="px-5 py-3 hidden md:table-cell">{{ __('credits.description') }}</th>
                    <th class="px-5 py-3 hidden lg:table-cell">{{ __('credits.date') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transactions as $txn)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-5 py-3">
                        @if($txn->type === 'purchase')
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ __('credits.type_purchase') }}</span>
                        @elseif($txn->type === 'usage')
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700">{{ __('credits.type_usage') }}</span>
                        @elseif($txn->type === 'refund')
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ __('credits.type_refund') }}</span>
                        @else
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ __('credits.type_grant') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-semibold {{ $txn->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $txn->amount >= 0 ? '+' : '' }}{{ number_format($txn->amount) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ number_format($txn->balance_after) }}</td>
                    <td class="px-5 py-3 text-gray-500 hidden md:table-cell">{{ $txn->description ?: '-' }}</td>
                    <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $txn->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-16 text-center text-gray-500">{{ __('credits.no_transactions') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
