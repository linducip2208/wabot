@extends('layouts.app')
@section('title', __('admin.coupons') . ' — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('admin.coupons') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.coupons_count', ['count' => $coupons->count()]) }}</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('admin.create_coupon') }}
    </button>
</div>

{{-- Stat Bar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @php $valid = $coupons->filter(fn($c) => $c->isValid()); @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-percent text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('admin.total_coupons') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $coupons->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('admin.valid_coupons') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $valid->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('common.used') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $coupons->sum('used_count') }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-ban text-red-400"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('admin.exhausted') }}</div><div class="text-xl font-extrabold text-gray-900">{{ $coupons->where('max_uses', '>', 0)->where('used_count', '>=', \Illuminate\Support\Facades\DB::raw('max_uses'))->count() }}</div></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.code') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('common.plan') }}</th>
                <th class="px-5 py-3">{{ __('admin.discount') }}</th>
                <th class="px-5 py-3">{{ __('common.usage') }}</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('admin.validity') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($coupons as $c)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3"><span class="font-mono text-xs font-semibold text-gray-900 bg-gray-100 px-2 py-1 rounded-md">{{ $c->code }}</span></td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell text-xs">{{ $c->plan?->name ?? __('admin.all_plans') }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium {{ $c->discount_type === 'percentage' ? 'text-violet-600' : 'text-orange-600' }}">
                        {{ $c->discount_type === 'percentage' ? $c->discount_value.'%' : 'Rp '.number_format($c->discount_value, 0, ',', '.') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-600 font-mono">{{ $c->used_count }}/{{ $c->max_uses > 0 ? $c->max_uses : '∞' }}</td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">
                    {{ $c->starts_at?->format('d M') ?? '-' }} — {{ $c->expires_at?->format('d M Y') ?? '-' }}
                </td>
                <td class="px-5 py-3">
                    <form method="POST" action="{{ route('admin.coupons.toggle', $c) }}" class="inline">
                        @csrf
                        <button class="text-xs font-medium px-2 py-0.5 rounded-full {{ $c->isValid() ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{ $c->isValid() ? __('common.active') : __('common.inactive') }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" class="inline" onsubmit="return confirm('{{ __('common.delete') }}?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-16 text-center text-gray-500">{{ __('admin.no_coupons') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="couponModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('admin.create_coupon') }}</h2>
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.code') }}</label>
                <input type="text" name="code" required placeholder="WELCOME2026" minlength="3" maxlength="50" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 uppercase">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.plan') }} ({{ __('admin.optional') }})</label>
                <select name="plan_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">{{ __('admin.all_plans') }}</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.discount_type') }}</label>
                    <select name="discount_type" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="percentage">{{ __('admin.percentage') }}</option>
                        <option value="fixed">{{ __('admin.fixed') }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.discount_value') }}</label>
                    <input type="number" name="discount_value" required min="1" step="1" value="10" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.min_order') }} (Rp)</label>
                    <input type="number" name="min_order" min="0" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.max_uses') }} (0=unlimited)</label>
                    <input type="number" name="max_uses" min="0" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.starts_at') }}</label>
                    <input type="datetime-local" name="starts_at" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('admin.expires_at') }}</label>
                    <input type="datetime-local" name="expires_at" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.create') }}</button>
            </div>
        </form>
    </div>
</div>

<script>function toggleModal(){document.getElementById('couponModal').classList.toggle('hidden');}</script>
@endsection
