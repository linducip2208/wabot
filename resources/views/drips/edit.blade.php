@extends('layouts.app')
@section('title', 'Edit Drip — WABot')
@section('content')

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('drips.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Edit Drip Campaign</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $dripCampaign->name }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('drips.update', $dripCampaign) }}" class="space-y-4">
        @csrf @method('PUT')
        <div>
            <label class="text-xs font-medium text-gray-500">Nama Campaign</label>
            <input type="text" name="name" value="{{ old('name', $dripCampaign->name) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Sesi / Agen</label>
            <select name="session_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                @foreach($sessions as $s)
                    <option value="{{ $s->id }}" {{ $dripCampaign->session_id==$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Status</label>
                <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1" {{ $dripCampaign->is_active ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ !$dripCampaign->is_active ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Target</label>
                <select name="send_to_new_only" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1" {{ $dripCampaign->send_to_new_only ? 'selected' : '' }}>Kontak Baru</option>
                    <option value="0" {{ !$dripCampaign->send_to_new_only ? 'selected' : '' }}>Semua Kontak</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('drips.steps', $dripCampaign) }}" class="flex-1 text-center bg-teal-50 text-teal-700 rounded-xl py-2.5 text-sm font-medium">Kelola Steps</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
