@extends('layouts.app')
@section('title', 'AI Agents — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">AI Agents</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $agents->count() }} agent · persona AI untuk membalas pelanggan</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Agent Baru
    </button>
</div>

@if(session('test_response'))
<div class="mb-4 bg-violet-50 border border-violet-200 rounded-xl p-4">
    <div class="text-xs font-semibold text-violet-700 mb-1">Test — "{{ session('test_message') }}"</div>
    <p class="text-sm text-gray-700">{{ session('test_response') }}</p>
</div>
@endif

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @php $roleMap = ['sales'=>['Sales','bg-emerald-50 text-emerald-700'],'support'=>['Support','bg-blue-50 text-blue-700'],'billing'=>['Billing','bg-amber-50 text-amber-700'],'general'=>['Umum','bg-gray-100 text-gray-600']]; @endphp
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
                <form method="POST" action="{{ route('ai-agents.destroy', $a) }}" onsubmit="return confirm('Hapus agent?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
        <p class="text-xs text-gray-500 line-clamp-2 mb-2">{{ $a->personality_prompt ?: 'Tanpa prompt persona' }}</p>
        <div class="text-[10px] text-gray-400 mb-3"><i class="fas fa-key mr-1"></i> {{ $a->aiKey?->name ?? 'Tanpa AI Key' }}</div>
        <form method="POST" action="{{ route('ai-agents.test', $a) }}" class="flex gap-1">
            @csrf
            <input type="text" name="message" required placeholder="Uji pesan..." class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
            <button class="text-xs bg-violet-50 text-violet-700 hover:bg-violet-100 px-2.5 py-1.5 rounded-lg font-medium"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-robot text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">Belum ada AI agent</p>
        <p class="text-sm text-gray-400 mb-4">Buat persona AI untuk otomasi percakapan</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> Agent Baru</button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="agentModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="agentModalTitle">AI Agent Baru</h2>
        <form method="POST" action="{{ route('ai-agents.store') }}" class="space-y-3" id="agentForm">
            @csrf
            <div id="agentMethod"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama Agent</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
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
                    @foreach($aiKeys as $k)<option value="{{ $k->id }}">{{ $k->name }} ({{ $k->provider }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Prompt Persona</label>
                <textarea name="personality_prompt" rows="3" placeholder="Kamu adalah asisten ramah yang..." class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Keyword Trigger <span class="text-gray-400">(pisah koma)</span></label>
                <input type="text" name="trigger_keywords" placeholder="beli, harga, promo" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('agentModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('agentModal'); m.classList.remove('hidden');
    document.getElementById('agentModalTitle').textContent = 'AI Agent Baru';
    const f = document.getElementById('agentForm'); f.action = '{{ route('ai-agents.store') }}'; f.reset();
    document.getElementById('agentMethod').innerHTML = '';
}
function editAgent(a) {
    const m = document.getElementById('agentModal'); m.classList.remove('hidden');
    document.getElementById('agentModalTitle').textContent = 'Edit AI Agent';
    const f = document.getElementById('agentForm'); f.action = '/ai-agents/' + a.id;
    f.querySelector('[name="name"]').value = a.name;
    f.querySelector('[name="role"]').value = a.role;
    f.querySelector('[name="ai_key_id"]').value = a.ai_key_id;
    f.querySelector('[name="personality_prompt"]').value = a.personality_prompt || '';
    f.querySelector('[name="trigger_keywords"]').value = a.trigger_keywords || '';
    document.getElementById('agentMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
