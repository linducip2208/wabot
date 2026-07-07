@extends('layouts.app')
@section('title', 'Deals — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Deals</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $deals->count() }} deal · total nilai Rp {{ number_format($deals->sum('value'), 0, ',', '.') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('deal-stages.index') }}" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-layer-group text-xs"></i> Stages</a>
        <a href="{{ route('deals.board') }}" class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2"><i class="fas fa-columns text-xs"></i> Kanban</a>
        <a href="{{ route('deals.create') }}" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2"><i class="fas fa-plus text-xs"></i> Deal Baru</a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Deal</th>
                <th class="px-5 py-3">Kontak</th>
                <th class="px-5 py-3">Stage</th>
                <th class="px-5 py-3">Nilai</th>
                <th class="px-5 py-3 hidden md:table-cell">Target Close</th>
                <th class="px-5 py-3 w-24 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($deals as $d)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3"><a href="{{ route('deals.show', $d) }}" class="font-medium text-gray-900 hover:text-brand-600">{{ $d->title }}</a></td>
                <td class="px-5 py-3 text-gray-600">{{ $d->contact?->name ?? '-' }}</td>
                <td class="px-5 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium text-white" style="background: {{ $d->stage?->color ?? '#6366f1' }}">{{ $d->stage?->name ?? '-' }}</span></td>
                <td class="px-5 py-3 font-semibold text-gray-800">Rp {{ number_format($d->value, 0, ',', '.') }}</td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400">{{ $d->expected_close_date?->format('d M Y') ?? '-' }}</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('deals.edit', $d) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                    <form method="POST" action="{{ route('deals.destroy', $d) }}" class="inline" onsubmit="return confirm('Hapus deal?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-handshake text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium">Belum ada deal</p>
                <p class="text-sm text-gray-400 mt-1">Lacak peluang penjualan Anda</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
