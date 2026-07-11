@extends('layouts.app')
@section('title', __('admin.affiliate_withdrawals') . ' — Admin')
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">{{ __('admin.affiliate_withdrawals') }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.withdrawals_count', ['count' => $withdrawals->count()]) }}</p>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">{{ __('common.user') }}</th>
                <th class="px-5 py-3">{{ __('common.amount') }}</th>
                <th class="px-5 py-3">{{ __('common.method') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('common.date') }}</th>
                <th class="px-5 py-3 w-32 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($withdrawals as $w)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 text-xs text-gray-400 font-mono">#{{ $w->id }}</td>
                <td class="px-5 py-3 font-medium text-gray-900">{{ $w->user->name }}</td>
                <td class="px-5 py-3 font-semibold text-gray-900">Rp {{ number_format($w->amount, 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-gray-600 text-xs">{{ $w->payment_method }}</td>
                <td class="px-5 py-3">
                    @if($w->status === 'pending')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ __('common.pending') }}</span>
                    @elseif($w->status === 'approved')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ __('common.approved') }}</span>
                    @else
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700">{{ __('common.rejected') }}</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $w->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right">
                    @if($w->status === 'pending')
                    <div class="flex items-center justify-end gap-1">
                        <form method="POST" action="{{ route('admin.affiliate-withdrawals.approve', $w) }}" class="inline">
                            @csrf
                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="{{ __('common.approve') }}">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.affiliate-withdrawals.reject', $w) }}" class="inline">
                            @csrf
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="{{ __('common.reject') }}">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="text-xs text-gray-400">{{ $w->processed_at?->format('d M Y') }}</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-16 text-center text-gray-500">{{ __('admin.no_withdrawals') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
