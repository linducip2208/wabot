@extends('layouts.app')
@section('title', 'Conversation Ratings — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Conversation Ratings</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $totalRatings }} penilaian dari pelanggan</p>
    </div>
</div>

<div class="grid md:grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex items-center gap-4 card-lift">
        <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-star text-amber-500 text-2xl"></i></div>
        <div>
            <div class="text-3xl font-extrabold text-gray-900">{{ $average }}</div>
            <div class="text-xs text-gray-500">Rata-rata dari 5</div>
        </div>
    </div>
    <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <div class="space-y-1.5">
            @for($i = 5; $i >= 1; $i--)
            @php $count = $distributionData[$i] ?? 0; $pct = $totalRatings > 0 ? round($count / $totalRatings * 100) : 0; @endphp
            <div class="flex items-center gap-2 text-xs">
                <span class="w-10 text-gray-500">{{ $i }} <i class="fas fa-star text-amber-400 text-[9px]"></i></span>
                <div class="flex-1 h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-amber-400" style="width: {{ $pct }}%"></div></div>
                <span class="w-10 text-right text-gray-600 font-medium">{{ $count }}</span>
            </div>
            @endfor
        </div>
    </div>
</div>

<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap items-end gap-3">
    <div>
        <label class="text-xs font-medium text-gray-500">Rating</label>
        <select name="rating" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
            <option value="">Semua</option>
            @for($i=5;$i>=1;$i--)<option value="{{ $i }}" {{ request('rating')==$i ? 'selected':'' }}>{{ $i }} bintang</option>@endfor
        </select>
    </div>
    <div><label class="text-xs font-medium text-gray-500">Dari</label><input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm"></div>
    <div><label class="text-xs font-medium text-gray-500">Sampai</label><input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-gray-300 px-3 py-2 text-sm"></div>
    <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-filter mr-1"></i> Filter</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider"><th class="px-5 py-3">Kontak</th><th class="px-5 py-3">Rating</th><th class="px-5 py-3">Komentar</th><th class="px-5 py-3 hidden md:table-cell">Tanggal</th><th class="px-5 py-3 w-16 text-right">Aksi</th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($ratings as $r)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 font-medium text-gray-800">{{ $r->contact?->name ?? '-' }}</td>
                <td class="px-5 py-3">
                    @for($i=1;$i<=5;$i++)<i class="fas fa-star text-xs {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-200' }}"></i>@endfor
                </td>
                <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $r->comment ?: '-' }}</td>
                <td class="px-5 py-3 hidden md:table-cell text-xs text-gray-400">{{ $r->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right"><a href="{{ route('ratings.show', $r) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-eye text-xs"></i></a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-star text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium">Belum ada rating</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $ratings->links() }}</div>
@endsection
