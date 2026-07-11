@extends('layouts.app')
@section('title', __('aiagents.edit_title'))
@section('content')

@php $aiKeys = $aiKeys ?? \App\Models\WaAiKey::where('user_id', auth()->id())->where('is_active', true)->get(); @endphp

<div class="flex items-center gap-3 mb-5">
    <a href="{{ route('ai-agents.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('aiagents.edit_agent') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $agent->name }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 p-6 max-w-lg">
    <form method="POST" action="{{ route('ai-agents.update', $agent) }}" class="space-y-4">
        @csrf @method('PUT')
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Agent</label>
                <input type="text" name="name" value="{{ old('name', $agent->name) }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.role') }}</label>
                <select name="{{ __('common.role') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    @foreach(['general'=>__('aiagents.role_general'),'sales'=>__('aiagents.role_sales'),'support'=>__('aiagents.role_support'),'billing'=>__('aiagents.role_billing')] as $v=>$l)
                        <option value="{{ $v }}" {{ $agent->role==$v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.ai_key') }}</label>
            <select name="ai_key_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                @foreach($aiKeys as $k)<option value="{{ $k->id }}" {{ $agent->ai_key_id==$k->id ? 'selected':'' }}>{{ $k->name }} ({{ $k->provider }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.personality_prompt') }}</label>
            <textarea name="personality_prompt" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">{{ old('personality_prompt', $agent->personality_prompt) }}</textarea>
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500">{{ __('aiagents.trigger_keywords') }} <span class="text-gray-400">({{ __('aiagents.separated_by_comma') }})</span></label>
            <input type="text" name="trigger_keywords" value="{{ old('trigger_keywords', $agent->trigger_keywords) }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
        </div>
        <div class="flex gap-2 pt-1">
            <a href="{{ route('ai-agents.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</a>
            <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
        </div>
    </form>
</div>
@endsection
