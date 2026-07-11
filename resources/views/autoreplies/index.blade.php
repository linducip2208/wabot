@extends('layouts.app')
@section('title', 'Auto-Reply — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Auto-Reply</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $autoreplies->count() }} {{ __('autoreplies.subtitle') }}</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('autoreplies.new_rule') }}
    </button>
</div>

<div class="grid gap-3">
    @forelse($autoreplies as $a)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                    {{ $a->match_type === 'exact' ? 'bg-rose-50' : ($a->match_type === 'contains' ? 'bg-sky-50' : ($a->match_type === 'welcome' ? 'bg-emerald-50' : ($a->match_type === 'fallback' ? 'bg-violet-50' : 'bg-amber-50'))) }}">
                    <i class="fas {{ $a->match_type === 'exact' ? 'fa-equals text-rose-500' : ($a->match_type === 'contains' ? 'fa-search text-sky-500' : ($a->match_type === 'welcome' ? 'fa-hand-sparkles text-emerald-500' : ($a->match_type === 'fallback' ? 'fa-reply-all text-violet-500' : 'fa-arrow-right text-amber-500'))) }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono px-2 py-0.5 rounded-md
                            {{ $a->match_type === 'exact' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($a->match_type === 'contains' ? 'bg-sky-50 text-sky-700 border border-sky-200' : ($a->match_type === 'welcome' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($a->match_type === 'fallback' ? 'bg-violet-50 text-violet-700 border border-violet-200' : 'bg-amber-50 text-amber-700 border border-amber-200'))) }}">
                            {{ ['exact'=>__('autoreplies.match_exact'),'contains'=>__('autoreplies.match_contains'),'starts_with'=>__('autoreplies.match_starts_with'),'welcome'=>__('autoreplies.match_welcome'),'fallback'=>__('autoreplies.match_fallback')][$a->match_type] }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $a->session?->name ?? __('common.all') . ' ' . __('common.session') }}</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $a->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $a->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $a->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                        @if($a->use_ai)
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700">
                            <i class="fas fa-robot text-[9px]"></i> AI
                        </span>
                        @endif
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-gray-500 mb-0.5">{{ $a->match_type === 'welcome' || $a->match_type === 'fallback' ? __('autoreplies.trigger') : __('autoreplies.keyword') }}</div>
                            <div class="font-mono text-sm text-gray-900 bg-gray-50 px-2.5 py-1 rounded-lg break-all">{{ $a->match_type === 'welcome' ? __('autoreplies.welcome_desc') : ($a->match_type === 'fallback' ? $a->keyword.' '.__('common.minute') : $a->keyword) }}</div>
                        </div>
                        <div class="text-gray-300 flex-shrink-0 self-stretch flex items-center"><i class="fas fa-arrow-right text-sm"></i></div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-gray-500 mb-0.5">{{ __('autoreplies.reply') }}</div>
                            <div class="text-sm text-gray-700 bg-green-50/50 px-2.5 py-1 rounded-lg break-all line-clamp-2">{{ $a->reply_message }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick='editRule({{ $a->id }}, "{{ addslashes($a->keyword) }}", "{{ addslashes($a->reply_message) }}", "{{ $a->match_type }}", {{ $a->session_id ?? 'null' }}, {{ $a->is_active ? 'true' : 'false' }}, {{ $a->use_ai ? 'true' : 'false' }}, {{ $a->ai_key_id ?? 'null' }})'
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('autoreplies.destroy', $a) }}" onsubmit="return confirm('{{ __('common.delete') }} rule?')">
                    @csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-robot text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium mb-1">{{ __('autoreplies.empty_title') }}</p>
        <p class="text-sm text-gray-400 mb-4">{{ __('autoreplies.empty_subtitle') }}</p>
        <button onclick="toggleModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> {{ __('autoreplies.new_rule') }}
        </button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="ruleModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="ruleModalTitle">{{ __('autoreplies.new_rule') }}</h2>
        <form method="POST" action="{{ route('autoreplies.store') }}" class="space-y-3" id="ruleForm">
            @csrf
            <div id="ruleMethodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div id="keywordGroup">
                    <label class="text-xs font-medium text-gray-500">{{ __('autoreplies.keyword') }}</label>
                    <input type="text" name="keyword" placeholder="hi" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('autoreplies.match_type') }}</label>
                    <select name="match_type" onchange="onMatchTypeChange(this.value)" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="contains">{{ __('autoreplies.match_contains') }}</option>
                        <option value="exact">{{ __('autoreplies.match_exact') }}</option>
                        <option value="starts_with">{{ __('autoreplies.match_starts_with') }}</option>
                        <option value="welcome">{{ __('autoreplies.match_welcome') }}</option>
                        <option value="fallback">{{ __('autoreplies.match_fallback') }}</option>
                    </select>
                </div>
            </div>
            <div id="cooldownGroup" class="hidden mt-3">
                <label class="text-xs font-medium text-gray-500">{{ __('autoreplies.cooldown') }}</label>
                <input type="number" name="fallback_cooldown" value="5" min="1" max="1440" placeholder="5" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('autoreplies.reply_help') }}</label>
                <textarea name="reply_message" rows="4" required placeholder="{Halo kak |Hai, selamat datang!|Halo, ada yang bisa dibantu?|Hai kak, terima kasih sudah menghubungi kami|Halo! Ada yang bisa kami bantu?|Selamat datang |Hai, senang bisa membantu|Halo, silakan sampaikan kebutuhannya|Hai kak, mohon tunggu sebentar ya|Halo, terima kasih sudah chat}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
                <button type="button" onclick="fillSpintaxSample()" class="mt-1.5 text-xs text-brand-600 hover:text-brand-700 font-medium"><i class="fas fa-wand-magic-sparkles text-[10px] mr-1"></i>{{ __('autoreplies.fill_sample') }}</button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.session') }} ({{ __('common.optional') }} = {{ __('common.all') }})</label>
                    <select name="session_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="">{{ __('common.all') }} {{ __('common.session') }}</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.status') }}</label>
                    <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="1">{{ __('common.active') }}</option>
                        <option value="0">{{ __('common.inactive') }}</option>
                    </select>
                </div>
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                <label class="flex items-center gap-2 cursor-pointer mb-2">
                    <input type="checkbox" name="use_ai" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" onchange="toggleAiMode(this)">
                    <span class="text-xs font-medium text-amber-800">{{ __('autoreplies.use_ai') }}</span>
                </label>
                <div id="aiKeyGroup" class="hidden">
                    <select name="ai_key_id" class="w-full rounded-xl border border-amber-300 px-3 py-2.5 text-sm">
                        <option value="">{{ __('aiagents.select_ai_key') }}</option>
                        @foreach($aiKeys as $k)
                            <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->provider }} / {{ $k->model }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const m = document.getElementById('ruleModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('ruleModalTitle').textContent = '{{ __('autoreplies.new_rule') }}';
        document.getElementById('ruleForm').action = '{{ route('autoreplies.store') }}';
        document.getElementById('ruleForm').querySelector('input[name="keyword"]').value = '';
        document.getElementById('ruleForm').querySelector('textarea[name="reply_message"]').value = '';
        document.getElementById('ruleForm').querySelector('select[name="match_type"]').value = 'contains';
        document.getElementById('ruleForm').querySelector('select[name="session_id"]').value = '';
        document.getElementById('ruleForm').querySelector('select[name="is_active"]').value = '1';
        document.getElementById('ruleForm').querySelector('input[name="use_ai"]').checked = false;
        document.getElementById('ruleForm').querySelector('select[name="ai_key_id"]').value = '';
        document.getElementById('aiKeyGroup').classList.add('hidden');
        document.getElementById('keywordGroup').classList.remove('hidden');
        document.getElementById('cooldownGroup').classList.add('hidden');
        document.getElementById('ruleForm').querySelector('input[name="fallback_cooldown"]').value = '5';
        toggleRequiredFields(false);
        document.getElementById('ruleMethodField').innerHTML = '';
    }
}
function editRule(id, keyword, reply, matchType, sessionId, isActive, useAi, aiKeyId) {
    const m = document.getElementById('ruleModal');
    m.classList.remove('hidden');
    document.getElementById('ruleModalTitle').textContent = '{{ __('autoreplies.edit_rule') }}';
    const f = document.getElementById('ruleForm');
    f.action = '/autoreplies/' + id;
    f.querySelector('input[name="keyword"]').value = keyword;
    f.querySelector('textarea[name="reply_message"]').value = reply;
    f.querySelector('select[name="match_type"]').value = matchType;
    f.querySelector('select[name="session_id"]').value = sessionId || '';
    f.querySelector('select[name="is_active"]').value = isActive ? '1' : '0';
    f.querySelector('input[name="use_ai"]').checked = useAi;
    f.querySelector('select[name="ai_key_id"]').value = aiKeyId || '';
    document.getElementById('aiKeyGroup').classList.toggle('hidden', !useAi);
    document.getElementById('keywordGroup').classList.toggle('hidden', matchType === 'welcome' || matchType === 'fallback');
    document.getElementById('cooldownGroup').classList.toggle('hidden', matchType !== 'fallback');
    if (matchType === 'fallback') {
        f.querySelector('input[name="fallback_cooldown"]').value = keyword || '5';
    }
    if (matchType === 'welcome' || matchType === 'fallback') {
        f.querySelector('input[name="keyword"]').removeAttribute('required');
    }
    toggleRequiredFields(useAi);
    document.getElementById('ruleMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}

function toggleAiMode(cb) {
    document.getElementById('aiKeyGroup').classList.toggle('hidden', !cb.checked);
    toggleRequiredFields(cb.checked);
}

function onMatchTypeChange(type) {
    const keywordGroup = document.getElementById('keywordGroup');
    const keywordInput = document.getElementById('ruleForm').querySelector('input[name="keyword"]');
    const cooldownGroup = document.getElementById('cooldownGroup');
    if (type === 'welcome') {
        keywordGroup.classList.add('hidden');
        cooldownGroup.classList.add('hidden');
        keywordInput.removeAttribute('required');
        keywordInput.value = '';
    } else if (type === 'fallback') {
        keywordGroup.classList.add('hidden');
        cooldownGroup.classList.remove('hidden');
        keywordInput.removeAttribute('required');
        keywordInput.value = '';
    } else {
        keywordGroup.classList.remove('hidden');
        cooldownGroup.classList.add('hidden');
        if (!document.getElementById('ruleForm').querySelector('input[name="use_ai"]').checked) {
            keywordInput.setAttribute('required', '');
        }
    }
}

function toggleRequiredFields(useAi) {
    const f = document.getElementById('ruleForm');
    f.querySelector('input[name="keyword"]').required = !useAi;
    f.querySelector('textarea[name="reply_message"]').required = !useAi;
    f.querySelector('select[name="ai_key_id"]').required = useAi;
}

function fillSpintaxSample() {
    const sample = '{Halo kak |Hai, selamat datang!|Halo, ada yang bisa dibantu?|Hai kak, terima kasih sudah menghubungi kami|Halo! Ada yang bisa kami bantu?|Selamat datang |Hai, senang bisa membantu|Halo, silakan sampaikan kebutuhannya|Hai kak, mohon tunggu sebentar ya|Halo, terima kasih sudah chat}';
    document.getElementById('ruleForm').querySelector('textarea[name="reply_message"]').value = sample;
}
</script>
@endsection
