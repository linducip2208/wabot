@extends('layouts.app')
@section('title', 'Intent Detection — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Intent Detection</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $intents->count() }} {{ __('intents.subtitle') }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('intents.new_intent') }}
    </button>
</div>

<div class="grid gap-3">
    @forelse($intents as $i)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0"><i class="fas fa-brain text-purple-500"></i></div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-900">{{ $i->name }}</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-purple-50 text-purple-700 border border-purple-200">{{ $i->intent_label }}</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $i->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $i->is_active ? __('common.active') : __('common.inactive') }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mb-1"><span class="text-gray-400">{{ __('intents.keywords_label') }}:</span> {{ $i->keywords }}</div>
                    @if($i->auto_reply)<div class="text-sm text-gray-700 bg-gray-50 rounded-lg px-2.5 py-1.5 line-clamp-2">{{ $i->auto_reply }}</div>@endif
                    @if($i->aiKey)<div class="text-[10px] text-gray-400 mt-1"><i class="fas fa-key mr-1"></i> {{ $i->aiKey->name }}</div>@endif
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick='editIntent(@json($i))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('intents.destroy', $i) }}" onsubmit="return confirm('{{ __('common.delete') }} intent?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-brain text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">{{ __('intents.empty_title') }}</p>
        <p class="text-sm text-gray-400 mb-4">{{ __('intents.empty_subtitle') }}</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> {{ __('intents.new_intent') }}</button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="intentModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="intentModalTitle">{{ __('intents.new_intent') }}</h2>
        <form method="POST" action="{{ route('intents.store') }}" class="space-y-3" id="intentForm">
            @csrf
            <div id="intentMethod"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('intents.intent_label') }}</label>
                    <input type="text" name="intent_label" required placeholder="beli_produk" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('intents.keywords_label') }} <span class="text-gray-400">({{ __('aiagents.separated_by_comma') }})</span></label>
                <input type="text" name="keywords" required placeholder="{{ __('intents.keywords_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('intents.auto_reply') }}</label>
                <textarea name="auto_reply" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('intents.ai_key') }}</label>
                <select name="ai_key_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">{{ __('intents.no_ai') }}</option>
                    @foreach($aiKeys as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('intentModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('intentModal'); m.classList.remove('hidden');
    document.getElementById('intentModalTitle').textContent = '{{ __('intents.new_intent') }}';
    const f = document.getElementById('intentForm'); f.action = '{{ route('intents.store') }}'; f.reset();
    document.getElementById('intentMethod').innerHTML = '';
}
function editIntent(i) {
    const m = document.getElementById('intentModal'); m.classList.remove('hidden');
    document.getElementById('intentModalTitle').textContent = '{{ __('intents.edit_intent') }}';
    const f = document.getElementById('intentForm'); f.action = '/intents/' + i.id;
    f.querySelector('[name="name"]').value = i.name;
    f.querySelector('[name="intent_label"]').value = i.intent_label;
    f.querySelector('[name="keywords"]').value = i.keywords;
    f.querySelector('[name="auto_reply"]').value = i.auto_reply || '';
    f.querySelector('[name="ai_key_id"]').value = i.ai_key_id || '';
    document.getElementById('intentMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
