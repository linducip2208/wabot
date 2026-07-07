@extends('layouts.app')
@section('title', 'AI Agent Baru — WABot')
@section('content')

@php $aiKeys = $aiKeys ?? \App\Models\WaAiKey::where('user_id', auth()->id())->where('is_active', true)->get(); @endphp

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ai-agents.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">AI Agent Baru</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat persona AI untuk otomasi percakapan</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('ai-agents.store') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">Nama Agent</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Role</label>
                <select name="role" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="general">Umum</option><option value="sales">Sales</option><option value="support">Support</option><option value="billing">Billing</option>
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">AI Key</label>
            <select name="ai_key_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <option value="">Pilih AI Key...</option>
                @foreach($aiKeys as $k)<option value="{{ $k->id }}" {{ old('ai_key_id')==$k->id ? 'selected':'' }}>{{ $k->name }} ({{ $k->provider }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Prompt Persona</label>
            <textarea name="personality_prompt" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">{{ old('personality_prompt') }}</textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Keyword Trigger <span class="text-gray-400">(pisah koma)</span></label>
            <input type="text" name="trigger_keywords" value="{{ old('trigger_keywords') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('ai-agents.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
        </div>
    </form>
</div>
@endsection
