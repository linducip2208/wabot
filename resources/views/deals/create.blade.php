@extends('layouts.app')
@section('title', 'Deal Baru — WABot')
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('deals.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Deal Baru</h1>
        <p class="text-sm text-gray-500 mt-0.5">Tambah peluang penjualan</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('deals.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs font-medium text-gray-500">Judul Deal</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Kontak</label>
                <select name="contact_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Pilih kontak...</option>
                    @foreach($contacts as $c)<option value="{{ $c->id }}" {{ old('contact_id')==$c->id ? 'selected':'' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Stage</label>
                <select name="stage_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Pilih stage...</option>
                    @foreach($stages as $s)<option value="{{ $s->id }}" {{ old('stage_id')==$s->id ? 'selected':'' }}>{{ $s->name }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Nilai (Rp)</label>
                <input type="number" name="value" min="0" step="0.01" value="{{ old('value', 0) }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Target Close</label>
                <input type="date" name="expected_close_date" value="{{ old('expected_close_date') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Catatan</label>
            <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">{{ old('notes') }}</textarea>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('deals.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
