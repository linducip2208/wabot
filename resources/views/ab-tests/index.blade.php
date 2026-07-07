@extends('layouts.app')
@section('title', 'A/B Test — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">A/B Test</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $tests->count() }} test · uji dua varian pesan untuk konversi terbaik</p>
    </div>
    <a href="{{ route('ab-tests.create') }}" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Test Baru
    </a>
</div>

<div class="grid gap-3">
    @forelse($tests as $t)
    @php
        $aRate = $t->a_sent > 0 ? round($t->a_replied / $t->a_sent * 100, 1) : 0;
        $bRate = $t->b_sent > 0 ? round($t->b_replied / $t->b_sent * 100, 1) : 0;
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-fuchsia-50 flex items-center justify-center"><i class="fas fa-flask text-fuchsia-500"></i></div>
                <div>
                    <div class="font-semibold text-gray-900">{{ $t->name }}</div>
                    <div class="text-xs text-gray-500">{{ $t->session?->name ?? '-' }}
                        @if($t->winner) · <span class="text-emerald-600 font-medium">Pemenang: {{ $t->winner === 'draw' ? 'Seri' : 'Varian '.$t->winner }}</span>@endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $t->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $t->is_active ? 'Berjalan' : 'Berhenti' }}
                </span>
                @if($t->is_active)
                <form method="POST" action="{{ route('ab-tests.end', $t) }}" class="inline">@csrf<button class="text-[11px] bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg font-medium">Akhiri</button></form>
                @else
                <form method="POST" action="{{ route('ab-tests.start', $t) }}" class="inline">@csrf<button class="text-[11px] bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg font-medium">Mulai</button></form>
                @endif
                <a href="{{ route('ab-tests.edit', $t) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="{{ route('ab-tests.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus test?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg border {{ $t->winner==='A' ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50' }} p-3">
                <div class="flex items-center justify-between mb-1"><span class="text-xs font-bold text-blue-600">Varian A</span><span class="text-xs text-gray-500">{{ $aRate }}% reply</span></div>
                <p class="text-xs text-gray-600 line-clamp-2">{{ $t->variant_a_message }}</p>
                <div class="text-[10px] text-gray-400 mt-1">{{ $t->a_replied }}/{{ $t->a_sent }} balasan</div>
            </div>
            <div class="rounded-lg border {{ $t->winner==='B' ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50' }} p-3">
                <div class="flex items-center justify-between mb-1"><span class="text-xs font-bold text-purple-600">Varian B</span><span class="text-xs text-gray-500">{{ $bRate }}% reply</span></div>
                <p class="text-xs text-gray-600 line-clamp-2">{{ $t->variant_b_message }}</p>
                <div class="text-[10px] text-gray-400 mt-1">{{ $t->b_replied }}/{{ $t->b_sent }} balasan</div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-flask text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">Belum ada A/B test</p>
        <p class="text-sm text-gray-400 mb-4">Bandingkan dua varian pesan untuk tahu mana yang paling efektif</p>
        <a href="{{ route('ab-tests.create') }}" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> Test Baru</a>
    </div>
    @endforelse
</div>
@endsection
