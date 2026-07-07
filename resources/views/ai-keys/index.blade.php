@extends('layouts.app')
@section('title', 'AI Keys — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">AI Provider Keys</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola kunci API AI untuk auto-reply & asisten</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah AI Key
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3">Provider</th>
                <th class="px-5 py-3 hidden md:table-cell">Model</th>
                <th class="px-5 py-3 hidden lg:table-cell">Max Token</th>
                <th class="px-5 py-3 w-32 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($keys as $k)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $k->name }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $k->provider === 'openai' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $k->provider === 'gemini' ? 'bg-blue-50 text-blue-700' : '' }}
                        {{ $k->provider === 'anthropic' ? 'bg-amber-50 text-amber-700' : '' }}
                        {{ $k->provider === 'openai_compatible' ? 'bg-violet-50 text-violet-700' : '' }}">
                        {{ str_replace('_', ' ', ucfirst($k->provider)) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $k->model }}</td>
                <td class="px-5 py-3 text-gray-600 hidden lg:table-cell">{{ $k->max_tokens }}</td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="{{ route('ai-keys.test', $k) }}" class="inline">
                        @csrf
                        <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="Test koneksi">
                            <i class="fas fa-flask text-xs"></i>
                        </button>
                    </form>
                    <button onclick='editKey({{ $k->id }}, {{ json_encode($k->name) }}, "{{ $k->provider }}", {{ json_encode($k->model) }}, {{ json_encode($k->base_url) }}, {{ json_encode($k->system_prompt) }}, {{ $k->max_tokens }}, {{ $k->temperature }})'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('ai-keys.destroy', $k) }}" class="inline" onsubmit="return confirm('Hapus AI key ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-16 text-center text-gray-500">Belum ada AI Provider Key. Tambahkan untuk mengaktifkan fitur AI.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
    <p class="font-medium mb-2"><i class="fas fa-info-circle text-brand-500 mr-1"></i> Provider yang didukung:</p>
    <div class="grid md:grid-cols-2 gap-2 text-xs">
        <div><span class="font-semibold">OpenAI</span> — GPT-4o, GPT-4o-mini, GPT-4-turbo</div>
        <div><span class="font-semibold">Gemini</span> — gemini-1.5-pro, gemini-1.5-flash</div>
        <div><span class="font-semibold">Anthropic</span> — claude-3-opus, claude-3-5-sonnet</div>
        <div><span class="font-semibold">OpenAI Compatible</span> — DeepSeek, Groq, Ollama, vLLM, dll</div>
    </div>
</div>

{{-- Modal --}}
<div id="keyModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle">Tambah AI Key</h2>
        <form method="POST" action="{{ route('ai-keys.store') }}" class="space-y-3" id="keyForm">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">Nama</label>
                <input type="text" name="name" placeholder="contoh: OpenAI Production" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Provider</label>
                <select name="provider" id="providerSelect" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm" onchange="toggleBaseUrl()">
                    <option value="">Pilih Provider</option>
                    <option value="openai">OpenAI</option>
                    <option value="deepseek">DeepSeek</option>
                    <option value="gemini">Gemini</option>
                    <option value="anthropic">Anthropic (Claude)</option>
                    <option value="openai_compatible">OpenAI Compatible (custom)</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Model</label>
                <input type="text" name="model" placeholder="contoh: gpt-4o" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div id="baseUrlGroup" class="hidden">
                <label class="text-xs font-medium text-gray-500">Base URL (OpenAI Compatible)</label>
                <input type="text" name="base_url" placeholder="https://api.openai.com/v1"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">API Key</label>
                <input type="password" name="api_key" placeholder="sk-..." required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-0.5">Dienskripsi sebelum disimpan. Kosongkan saat edit jika tidak ingin mengubah.</p>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">System Prompt (opsional)</label>
                <textarea name="system_prompt" rows="2" placeholder="Instruksi sistem untuk AI..." maxlength="2000"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Max Tokens</label>
                    <input type="number" name="max_tokens" value="1024" min="1" max="128000"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Temperature</label>
                    <input type="number" name="temperature" value="0.7" step="0.1" min="0" max="2"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const m = document.getElementById('keyModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('modalTitle').textContent = 'Tambah AI Key';
        const f = document.getElementById('keyForm');
        f.action = '{{ route('ai-keys.store') }}';
        f.reset();
        f.querySelector('input[name="max_tokens"]').value = '1024';
        f.querySelector('input[name="temperature"]').value = '0.7';
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('baseUrlGroup').classList.add('hidden');
    }
}
function toggleBaseUrl() {
    const v = document.getElementById('providerSelect').value;
    const show = v === 'openai_compatible' || v === 'deepseek';
    document.getElementById('baseUrlGroup').classList.toggle('hidden', !show);
    if (v === 'deepseek') {
        const baseUrlEl = document.querySelector('input[name="base_url"]');
        if (!baseUrlEl.value) baseUrlEl.value = 'https://api.deepseek.com';
        const modelEl = document.querySelector('input[name="model"]');
        if (!modelEl.value) modelEl.value = 'deepseek-chat';
    }
}
function editKey(id, name, provider, model, baseUrl, systemPrompt, maxTokens, temperature) {
    const m = document.getElementById('keyModal');
    m.classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit AI Key';
    const f = document.getElementById('keyForm');
    f.action = '/ai-keys/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('select[name="provider"]').value = provider;
    f.querySelector('input[name="model"]').value = model;
    f.querySelector('input[name="base_url"]').value = baseUrl || '';
    f.querySelector('input[name="api_key"]').value = '';
    f.querySelector('input[name="api_key"]').required = false;
    f.querySelector('textarea[name="system_prompt"]').value = systemPrompt || '';
    f.querySelector('input[name="max_tokens"]').value = maxTokens;
    f.querySelector('input[name="temperature"]').value = temperature;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    toggleBaseUrl();
}
</script>
@endsection
