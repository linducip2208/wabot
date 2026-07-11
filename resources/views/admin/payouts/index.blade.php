@extends('layouts.app')
@section('title', 'Payout — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('admin.payout_mgmt') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.payout_count', ['count' => $payouts->count()]) }}</p>
    </div>
</div>

{{-- Stat Bar --}}
@php
    $pending = $payouts->where('status', 'pending');
    $completed = $payouts->where('status', 'completed');
    $rejected = $payouts->where('status', 'rejected');
    $totalCompleted = $completed->sum('amount');
@endphp
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-list text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('common.total') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $payouts->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('common.pending') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $pending->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('common.completed') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $completed->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-money-bill-wave text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('admin.disbursed') }}</div><div class="text-xl font-extrabold text-gray-900">Rp {{ number_format($totalCompleted, 0, ',', '.') }}</div></div>
    </div>
</div>

{{-- Filter --}}
<div class="flex gap-2 mb-4 flex-wrap">
    <a href="{{ route('admin.payouts.index') }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ !request('status') ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ __('common.all') }}</a>
    <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ __('common.pending') }}</a>
    <a href="{{ route('admin.payouts.index', ['status' => 'completed']) }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') === 'completed' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ __('common.completed') }}</a>
    <a href="{{ route('admin.payouts.index', ['status' => 'rejected']) }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') === 'rejected' ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }} transition">{{ __('common.rejected') }}</a>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3">{{ __('common.amount') }}</th>
                <th class="px-5 py-3">{{ __('common.method') }}</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('admin.account_info') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('common.submitted') }}</th>
                <th class="px-5 py-3 w-32 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($payouts as $p)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3 text-gray-500 text-xs font-mono">#{{ $p->id }}</td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold" style="background: {{ collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($p->user?->name ?? '') % 6) }}">
                            {{ strtoupper(substr($p->user?->name ?? 'U', 0, 2)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $p->user?->name ?? 'N/A' }}</span>
                    </div>
                </td>
                <td class="px-5 py-3 font-semibold text-gray-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $p->method === 'paypal' ? 'bg-sky-50 text-sky-700' : 'bg-violet-50 text-violet-700' }}">
                        {{ $p->method === 'paypal' ? 'PayPal' : 'Bank' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden lg:table-cell max-w-[160px] truncate">{{ $p->account_info }}</td>
                <td class="px-5 py-3">
                    @if($p->status === 'pending')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ __('common.pending') }}</span>
                    @elseif($p->status === 'completed')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ __('common.completed') }}</span>
                    @else
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700">{{ __('common.rejected') }}</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden md:table-cell">{{ $p->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3 text-right">
                    @if($p->status === 'pending')
                    <div class="flex items-center justify-end gap-1">
                        <form method="POST" action="{{ route('admin.payouts.approve', $p) }}" class="inline" onsubmit="return confirm('{{ __('common.approve') }} payout #{{ $p->id }} ke {{ $p->user?->name }}?')">
                            @csrf
                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="{{ __('common.approve') }}"><i class="fas fa-check text-xs"></i></button>
                        </form>
                        <button onclick="rejectPayout({{ $p->id }})"
                            class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="{{ __('common.reject') }}"><i class="fas fa-times text-xs"></i></button>
                    </div>
                    @elseif($p->admin_note)
                        <span class="text-xs text-gray-400 cursor-help" title="{{ $p->admin_note }}"><i class="fas fa-info-circle"></i></span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-16 text-center text-gray-500">{{ __('admin.no_payouts') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('admin.reject_payout') }}</h2>
        <form method="POST" id="rejectForm" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('admin.rejection_reason') }}</label>
                <textarea name="admin_note" rows="2" required placeholder="{{ __('admin.rejection_reason_placeholder') }}"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
            </div>
            <div class="flex gap-2">
                <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-red-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-red-700">{{ __('common.reject') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function rejectPayout(id) {
    document.getElementById('rejectForm').action = '/admin/payouts/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
}
</script>
@endsection
