@extends('layouts.app')
@section('title', __('affiliate.page_title'))
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">{{ __('affiliate.page_title') }}</h1>
    <p class="text-sm text-gray-500 mt-0.5">{{ __('affiliate.subtitle') }}</p>
</div>

{{-- Referral Link --}}
<div class="bg-white rounded-xl border border-gray-200 p-5 mb-5">
    <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl bg-brand-100 flex items-center justify-center"><i class="fas fa-link text-brand-600"></i></div>
        <div>
            <h2 class="font-semibold text-gray-900">{{ __('affiliate.your_referral_link') }}</h2>
            <p class="text-xs text-gray-500">{{ __('affiliate.share_and_earn') }}</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <input type="text" id="referralLink" value="{{ $referralLink }}" readonly
            class="flex-1 bg-gray-50 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-mono text-gray-700 focus:ring-2 focus:ring-brand-500">
        <button onclick="copyReferralLink()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2 flex-shrink-0">
            <i class="fas fa-copy text-xs"></i> {{ __('common.copy') }}
        </button>
    </div>
    <div id="copyFeedback" class="text-xs text-emerald-600 mt-2 hidden"><i class="fas fa-check mr-1"></i> {{ __('common.copied') }}</div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-users text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('affiliate.total_referrals') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $summary['total_referrals'] }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-coins text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('affiliate.total_commission') }}</div><div class="text-xl font-extrabold text-gray-900">Rp {{ number_format($summary['total_commissions'], 0, ',', '.') }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('affiliate.pending') }}</div><div class="text-xl font-extrabold text-gray-900">Rp {{ number_format($summary['pending'], 0, ',', '.') }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-check-double text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('affiliate.paid') }}</div><div class="text-xl font-extrabold text-gray-900">Rp {{ number_format($summary['paid'], 0, ',', '.') }}</div></div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- Commissions Table --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">{{ __('affiliate.commissions') }}</h2>
                <span class="text-xs text-gray-400">{{ $commissions->count() }} {{ __('affiliate.entries') }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th class="px-5 py-3">{{ __('affiliate.referred_user') }}</th>
                            <th class="px-5 py-3">{{ __('affiliate.amount') }}</th>
                            <th class="px-5 py-3">{{ __('affiliate.rate') }}</th>
                            <th class="px-5 py-3">{{ __('common.status') }}</th>
                            <th class="px-5 py-3 hidden lg:table-cell">{{ __('common.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($commissions as $c)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-3 text-gray-900 font-medium">{{ $c->referredUser?->name ?? 'User #'.$c->referred_user_id }}</td>
                            <td class="px-5 py-3 font-semibold text-emerald-600">Rp {{ number_format($c->amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ $c->rate }}%</td>
                            <td class="px-5 py-3">
                                @if($c->status === 'pending')
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ __('common.pending') }}</span>
                                @else
                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ __('common.paid') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $c->created_at->format('d M Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-gray-500">{{ __('affiliate.no_commissions') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Withdrawal Panel --}}
    <div class="space-y-4">
        {{-- Request Withdrawal --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('affiliate.request_withdrawal') }}</h2>
            <div class="bg-gray-50 rounded-xl p-3 mb-3 flex items-center justify-between">
                <span class="text-xs text-gray-500">{{ __('affiliate.available') }}</span>
                <span class="text-sm font-bold text-gray-900">Rp {{ number_format($summary['pending'], 0, ',', '.') }}</span>
            </div>
            <form method="POST" action="{{ route('affiliate.withdrawal.request') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.amount') }} (Rp)</label>
                    <input type="number" name="amount" min="10000" step="1000" placeholder="100000" required
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.method') }}</label>
                    <select name="payment_method" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="bank_transfer">{{ __('affiliate.bank_transfer') }}</option>
                        <option value="paypal">PayPal</option>
                        <option value="ewallet">{{ __('affiliate.ewallet') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('affiliate.payment_details') }}</label>
                    <textarea name="payment_details" rows="2" required placeholder="{{ __('affiliate.details_placeholder') }}"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700 transition">
                    {{ __('affiliate.submit_withdrawal') }}
                </button>
            </form>
        </div>

        {{-- Withdrawal History --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-900 mb-3">{{ __('affiliate.withdrawal_history') }}</h2>
            @forelse($withdrawals as $w)
            <div class="py-2 border-b border-gray-100 last:border-0">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-900">Rp {{ number_format($w->amount, 0, ',', '.') }}</span>
                    @if($w->status === 'pending')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">{{ __('common.pending') }}</span>
                    @elseif($w->status === 'approved')
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">{{ __('common.approved') }}</span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-700">{{ __('common.rejected') }}</span>
                    @endif
                </div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $w->payment_method }} · {{ $w->created_at->format('d M Y') }}</div>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">{{ __('affiliate.no_withdrawals') }}</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    document.getElementById('copyFeedback').classList.remove('hidden');
    setTimeout(() => document.getElementById('copyFeedback').classList.add('hidden'), 2000);
}
</script>
@endpush
@endsection
