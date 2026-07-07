@extends('layouts.app')
@section('title', 'Drip Campaign — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Drip Campaign</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $campaigns->count() }} campaign · pesan berurutan otomatis ke kontak baru</p>
    </div>
    <a href="{{ route('drips.create') }}" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Drip Baru
    </a>
</div>

<div class="grid gap-3">
    @forelse($campaigns as $c)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center"><i class="fas fa-stream text-teal-500"></i></div>
                <div>
                    <div class="font-semibold text-gray-900">{{ $c->name }}</div>
                    <div class="text-xs text-gray-500">{{ $c->session?->name ?? '-' }} · {{ $c->steps_count }} step</div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $c->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $c->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                    {{ $c->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                <a href="{{ route('drips.steps', $c) }}" class="text-[11px] bg-teal-50 text-teal-700 hover:bg-teal-100 px-2.5 py-1.5 rounded-lg font-medium"><i class="fas fa-list-ol"></i> Steps</a>
                <a href="{{ route('drips.edit', $c) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="{{ route('drips.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus drip campaign?')">
                    @csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-stream text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">Belum ada drip campaign</p>
        <p class="text-sm text-gray-400 mb-4">Kirim rangkaian pesan otomatis dengan jeda waktu</p>
        <a href="{{ route('drips.create') }}" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> Drip Baru</a>
    </div>
    @endforelse
</div>
@endsection
