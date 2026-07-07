@extends('layouts.app')
@section('title', 'Interactive Buttons — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Interactive Buttons</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $buttons->count() }} template · pesan dengan tombol interaktif</p>
    </div>
    <a href="{{ route('buttons.create') }}" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Button Baru
    </a>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @forelse($buttons as $b)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center"><i class="fas fa-hand-pointer text-rose-500"></i></div>
                <div class="font-semibold text-gray-900 text-sm">{{ $b->name }}</div>
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('buttons.edit', $b) }}" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></a>
                <form method="POST" action="{{ route('buttons.destroy', $b) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <div class="rounded-lg bg-gray-50 border border-gray-100 p-3 text-sm">
            @if($b->header_text)<div class="font-semibold text-gray-800 mb-1">{{ $b->header_text }}</div>@endif
            <p class="text-gray-600 text-xs mb-2 line-clamp-3">{{ $b->body_text }}</p>
            @if($b->footer_text)<div class="text-[10px] text-gray-400 mb-2">{{ $b->footer_text }}</div>@endif
            <div class="space-y-1">
                @foreach(($b->buttons ?? []) as $btn)
                    <div class="w-full text-center text-xs text-brand-600 border border-brand-200 rounded-md py-1 bg-white">{{ is_array($btn) ? ($btn['text'] ?? $btn['title'] ?? 'Tombol') : $btn }}</div>
                @endforeach
            </div>
        </div>
        <div class="text-[10px] text-gray-400 mt-2">{{ $b->session?->name ?? 'Semua sesi' }}</div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-hand-pointer text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">Belum ada interactive button</p>
        <p class="text-sm text-gray-400 mb-4">Buat pesan dengan tombol untuk mempermudah respons pelanggan</p>
        <a href="{{ route('buttons.create') }}" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> Button Baru</a>
    </div>
    @endforelse
</div>
@endsection
