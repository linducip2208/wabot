@extends('layouts.app')
@section('title', __('aiagents.create_title'))
@section('content')

@php $aiKeys = $aiKeys ?? \App\Models\WaAiKey::where('user_id', auth()->id())->where('is_active', true)->get(); @endphp

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ai-agents.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('aiagents.new_agent') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('aiagents.empty_subtitle') }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('ai-agents.store') }}" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Agent</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.role') }}</label>
                <select name="{{ __('common.role') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="general">{{ __('aiagents.role_general') }}</option><option value="sales">{{ __('aiagents.role_sales') }}</option><option value="support">{{ __('aiagents.role_support') }}</option><option value="billing">{{ __('aiagents.role_billing') }}</option>
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.ai_key') }}</label>
            <select name="ai_key_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                <option value="">{{ __('aiagents.select_ai_key') }}</option>
                @foreach($aiKeys as $k)<option value="{{ $k->id }}" {{ old('ai_key_id')==$k->id ? 'selected':'' }}>{{ $k->name }} ({{ $k->provider }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.personality_prompt') }}</label>
            <textarea name="personality_prompt" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">{{ old('personality_prompt') }}</textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.trigger_keywords') }} <span class="text-gray-400">({{ __('aiagents.separated_by_comma') }})</span></label>
            <input type="text" name="trigger_keywords" value="{{ old('trigger_keywords') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">Channels</label>
            <div class="grid grid-cols-2 gap-2 mt-1">
                @php $ch = old('channels', []); @endphp
                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="channels[]" value="whatsapp" {{ in_array('whatsapp', $ch) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700"><i class="fab fa-whatsapp text-green-500 mr-1"></i> WhatsApp</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="channels[]" value="meta" {{ in_array('meta', $ch) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700"><i class="fab fa-facebook text-blue-500 mr-1"></i> Meta</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="channels[]" value="instagram" {{ in_array('instagram', $ch) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700"><i class="fab fa-instagram text-pink-500 mr-1"></i> Instagram</span>
                </label>
                <label class="flex items-center gap-2 p-2 rounded-xl border border-gray-200 cursor-pointer hover:bg-gray-50">
                    <input type="checkbox" name="channels[]" value="telegram" {{ in_array('telegram', $ch) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500">
                    <span class="text-sm text-gray-700"><i class="fab fa-telegram-plane text-blue-400 mr-1"></i> Telegram</span>
                </label>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">Leave empty to activate on all channels</p>
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('ai-agents.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection
