@extends('layouts.app')
@section('title', 'A/B Test Baru — WABot')
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ab-tests.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">A/B Test Baru</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat dua varian pesan untuk dibandingkan</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('ab-tests.store') }}" class="space-y-4">
        @csrf
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-medium text-gray-500">Nama Test</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Sesi / Agen</label>
                <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Pilih sesi...</option>
                    @foreach($sessions as $s)<option value="{{ $s->id }}" {{ old('session_id')==$s->id ? 'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
                @error('session_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="border border-blue-200 rounded-xl p-3 bg-blue-50/30">
                <label class="text-xs font-bold text-blue-600">Varian A</label>
                <textarea name="variant_a_message" rows="3" required placeholder="Pesan varian A" class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ old('variant_a_message') }}</textarea>
                <input type="url" name="media_url_a" value="{{ old('media_url_a') }}" placeholder="Media URL A (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="border border-purple-200 rounded-xl p-3 bg-purple-50/30">
                <label class="text-xs font-bold text-purple-600">Varian B</label>
                <textarea name="variant_b_message" rows="3" required placeholder="Pesan varian B" class="w-full mt-1 rounded-xl border border-gray-300 px-3 py-2 text-sm">{{ old('variant_b_message') }}</textarea>
                <input type="url" name="media_url_b" value="{{ old('media_url_b') }}" placeholder="Media URL B (opsional)" class="w-full mt-2 rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('ab-tests.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
