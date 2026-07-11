@extends('layouts.app')
@section('title', __('admin.credit_transactions') . ' — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('admin.credit_transactions') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.all_credit_transactions') }}</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-gift text-xs"></i> {{ __('admin.grant_credits') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.user') }}</th>
                <th class="px-5 py-3">{{ __('credits.type') }}</th>
                <th class="px-5 py-3">{{ __('credits.amount') }}</th>
                <th class="px-5 py-3">{{ __('credits.balance') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('credits.description') }}</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('common.date') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($transactions as $txn)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $txn->user->name }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $txn->type === 'purchase' ? 'bg-emerald-50 text-emerald-700' : '' }}{{ $txn->type === 'usage' ? 'bg-red-50 text-red-700' : '' }}{{ $txn->type === 'admin_grant' ? 'bg-blue-50 text-blue-700' : '' }}{{ $txn->type === 'refund' ? 'bg-amber-50 text-amber-700' : '' }}">
                        {{ __('credits.type_'.$txn->type) }}
                    </span>
                </td>
                <td class="px-5 py-3 font-semibold {{ $txn->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $txn->amount >= 0 ? '+' : '' }}{{ number_format($txn->amount) }}
                </td>
                <td class="px-5 py-3 text-gray-600 font-mono">{{ number_format($txn->balance_after) }}</td>
                <td class="px-5 py-3 text-gray-500 hidden md:table-cell text-xs max-w-[200px] truncate">{{ $txn->description ?: '-' }}</td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $txn->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-16 text-center text-gray-500">{{ __('credits.no_transactions') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transactions->hasPages())
<div class="mt-4">{{ $transactions->links() }}</div>
@endif

<div id="grantModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('admin.grant_credits') }}</h2>
        <form method="POST" action="{{ route('admin.credit-transactions.grant') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.user') }} (ID)</label>
                <input type="number" name="user_id" required placeholder="1" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('credits.amount') }}</label>
                <input type="number" name="amount" required min="1" value="10" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('credits.description') }} ({{ __('admin.optional') }})</label>
                <input type="text" name="description" maxlength="255" placeholder="Admin grant" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('admin.grant') }}</button>
            </div>
        </form>
    </div>
</div>

<script>function toggleModal(){document.getElementById('grantModal').classList.toggle('hidden');}</script>
@endsection
