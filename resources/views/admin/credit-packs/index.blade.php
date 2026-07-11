@extends('layouts.app')
@section('title', __('admin.credit_packs') . ' — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('admin.credit_packs') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('admin.credit_packs_count', ['count' => $packs->count()]) }}</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('admin.create_pack') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.name') }}</th>
                <th class="px-5 py-3">{{ __('credits.credits') }}</th>
                <th class="px-5 py-3">{{ __('common.price') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3">{{ __('common.order') }}</th>
                <th class="px-5 py-3 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($packs as $pack)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $pack->name }}</td>
                <td class="px-5 py-3 font-semibold text-gray-900">{{ number_format($pack->credits) }}</td>
                <td class="px-5 py-3 text-gray-600">Rp {{ number_format($pack->price, 0, ',', '.') }}</td>
                <td class="px-5 py-3">
                    <form method="POST" action="{{ route('admin.credit-packs.toggle', $pack) }}" class="inline">
                        @csrf
                        <button class="text-xs font-medium px-2 py-0.5 rounded-full {{ $pack->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                            {{ $pack->is_active ? __('common.active') : __('common.inactive') }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $pack->sort_order }}</td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="{{ route('admin.credit-packs.destroy', $pack) }}" class="inline" onsubmit="return confirm('{{ __('common.delete') }}?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-16 text-center text-gray-500">{{ __('admin.no_credit_packs') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div id="packModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('admin.create_pack') }}</h2>
        <form method="POST" action="{{ route('admin.credit-packs.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" required placeholder="Paket Basic" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('credits.credits') }}</label>
                <input type="number" name="credits" required min="1" value="10" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.price') }} (Rp)</label>
                <input type="number" name="price" required min="0" step="1000" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.order') }}</label>
                <input type="number" name="sort_order" min="0" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.create') }}</button>
            </div>
        </form>
    </div>
</div>

<script>function toggleModal(){document.getElementById('packModal').classList.toggle('hidden');}</script>
@endsection
