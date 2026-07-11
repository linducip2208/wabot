@extends('layouts.app')
@section('title', 'AI Agents — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">AI Agents</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $agents->count() }} {{ __('aiagents.subtitle_count') }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('aiagents.new_agent') }}
    </button>
</div>

@if(session('test_response'))
<div class="mb-4 bg-violet-50 border border-violet-200 rounded-xl p-4">
    <div class="text-xs font-semibold text-violet-700 mb-1">{{ __('aiagents.test_response') }} &mdash; "{{ session('test_message') }}"</div>
    <p class="text-sm text-gray-700">{{ session('test_response') }}</p>
</div>
@endif

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @php $roleMap = ['sales'=>[__('aiagents.role_sales'),'bg-emerald-50 text-emerald-700'],'support'=>[__('aiagents.role_support'),'bg-blue-50 text-blue-700'],'billing'=>[__('aiagents.role_billing'),'bg-amber-50 text-amber-700'],'general'=>[__('aiagents.role_general'),'bg-gray-100 text-gray-600']]; @endphp
    @forelse($agents as $a)
    @php $r = $roleMap[$a->role] ?? [$a->role,'bg-gray-100 text-gray-600']; @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-robot text-violet-500"></i></div>
                <div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $a->name }}</div>
                    <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium {{ $r[1] }}">{{ $r[0] }}</span>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button onclick='editAgent(@json($a))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('ai-agents.destroy', $a) }}" onsubmit="return confirm('{{ __('common.delete') }} agent?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ $a->personality_prompt ?: __('aiagents.no_persona') }}</p>
        <div class="text-[10px] text-gray-400 mb-3"><i class="fas fa-key mr-1"></i> {{ $a->aiKey?->name ?? __('aiagents.no_ai_key') }}</div>
        <form method="POST" action="{{ route('ai-agents.test', $a) }}" class="flex gap-1">
            @csrf
            <input type="text" name="message" required placeholder="{{ __('aiagents.test_message') }}" class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
            <button class="text-xs bg-violet-50 text-violet-700 hover:bg-violet-100 px-2.5 py-1.5 rounded-lg font-medium"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-robot text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">{{ __('aiagents.empty_title') }}</p>
        <p class="text-sm text-gray-400 mb-4">{{ __('aiagents.empty_subtitle') }}</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> {{ __('aiagents.new_agent') }}</button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="agentModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="agentModalTitle">{{ __('aiagents.new_agent') }}</h2>
        <form method="POST" action="{{ route('ai-agents.store') }}" class="space-y-3" id="agentForm">
            @csrf
            <div id="agentMethod"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Agent</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
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
                    @foreach($aiKeys as $k)<option value="{{ $k->id }}">{{ $k->name }} ({{ $k->provider }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('aiagents.personality_prompt') }}</label>
                <textarea name="personality_prompt" rows="3" placeholder="{{ __('aiagents.persona_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('aiagents.trigger_keywords') }} <span class="text-gray-400">({{ __('aiagents.separated_by_comma') }})</span></label>
                <input type="text" name="trigger_keywords" placeholder="{{ __('aiagents.trigger_keywords_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('agentModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('agentModal'); m.classList.remove('hidden');
    document.getElementById('agentModalTitle').textContent = '{{ __('aiagents.new_agent') }}';
    const f = document.getElementById('agentForm'); f.action = '{{ route('ai-agents.store') }}'; f.reset();
    document.getElementById('agentMethod').innerHTML = '';
}
function editAgent(a) {
    const m = document.getElementById('agentModal'); m.classList.remove('hidden');
    document.getElementById('agentModalTitle').textContent = '{{ __('aiagents.edit_agent') }}';
    const f = document.getElementById('agentForm'); f.action = '/ai-agents/' + a.id;
    f.querySelector('[name="name"]').value = a.name;
    f.querySelector('[name="{{ __('common.role') }}"]').value = a.{{ __('common.role') }};
    f.querySelector('[name="ai_key_id"]').value = a.ai_key_id;
    f.querySelector('[name="personality_prompt"]').value = a.personality_prompt || '';
    f.querySelector('[name="trigger_keywords"]').value = a.trigger_keywords || '';
    document.getElementById('agentMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
