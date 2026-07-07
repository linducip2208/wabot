@extends('layouts.app')
@section('title', 'Flow Builder — ' . $flow->name)
@section('content')

<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('flows.index') }}" class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:text-brand-600"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-extrabold text-gray-900">{{ $flow->name }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Susun node percakapan · trigger <span class="font-mono bg-gray-100 px-1.5 rounded">{{ $flow->trigger_keyword }}</span></p>
        </div>
    </div>
</div>

<div x-data="flowBuilder()" class="space-y-4">
    <div class="flex flex-wrap items-center gap-2 bg-white rounded-xl border border-gray-200 p-3">
        <span class="text-xs font-semibold text-gray-500 mr-1">Tambah Node:</span>
        <button type="button" @click="addNode('message')" class="text-xs bg-sky-50 text-sky-700 hover:bg-sky-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-comment-dots mr-1"></i> Pesan</button>
        <button type="button" @click="addNode('condition')" class="text-xs bg-amber-50 text-amber-700 hover:bg-amber-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-code-branch mr-1"></i> Kondisi</button>
        <button type="button" @click="addNode('ai')" class="text-xs bg-violet-50 text-violet-700 hover:bg-violet-100 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-robot mr-1"></i> AI</button>
        <button type="button" @click="addNode('wait')" class="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-medium"><i class="fas fa-hourglass-half mr-1"></i> Tunggu</button>
    </div>

    <form method="POST" action="{{ route('flows.nodes.store', $flow) }}" @submit="prepareSubmit">
        @csrf
        <div id="nodesPayload"></div>

        <div class="space-y-3">
            <template x-for="(node, idx) in nodes" :key="idx">
                <div class="bg-white rounded-xl border border-gray-200 p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold"
                                :class="{'bg-sky-500': node.type==='message','bg-amber-500': node.type==='condition','bg-violet-500': node.type==='ai','bg-gray-500': node.type==='wait'}"
                                x-text="idx+1"></span>
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500" x-text="typeLabel(node.type)"></span>
                        </div>
                        <button type="button" @click="removeNode(idx)" class="text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </div>
                    <div class="grid gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500">Label</label>
                            <input type="text" x-model="node.label" required placeholder="Nama node" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                        </div>
                        {{-- message / ai --}}
                        <template x-if="node.type==='message' || node.type==='ai'">
                            <div>
                                <label class="text-xs font-medium text-gray-500" x-text="node.type==='ai' ? 'Prompt AI' : 'Pesan Balasan'"></label>
                                <textarea x-model="node.reply_message" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"></textarea>
                            </div>
                        </template>
                        <template x-if="node.type==='ai'">
                            <div>
                                <label class="text-xs font-medium text-gray-500">AI Key</label>
                                <select x-model="node.ai_key_id" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value="">Pilih AI Key...</option>
                                    @foreach($aiKeys as $k)
                                        <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->provider }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>
                        <template x-if="node.type==='condition'">
                            <div class="grid grid-cols-3 gap-2">
                                <input type="text" x-model="node.condition_field" placeholder="Field (mis. pesan)" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                <select x-model="node.condition_operator" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                                    <option value="equals">=</option>
                                    <option value="contains">mengandung</option>
                                    <option value="starts_with">diawali</option>
                                </select>
                                <input type="text" x-model="node.condition_value" placeholder="Nilai" class="rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </template>
                        <template x-if="node.type==='wait'">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tunggu (detik)</label>
                                <input type="number" x-model="node.wait_seconds" min="1" placeholder="5" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="nodes.length===0" class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <i class="fas fa-sitemap text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Belum ada node. Tambahkan node dari tombol di atas.</p>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" x-show="nodes.length>0" class="bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700"><i class="fas fa-save mr-1"></i> Simpan Flow</button>
        </div>
    </form>
</div>

<script>
function flowBuilder() {
    return {
        nodes: @json($flow->nodes->map(fn($n) => [
            'id' => $n->id, 'type' => $n->type, 'label' => $n->label,
            'reply_message' => $n->reply_message, 'ai_key_id' => $n->ai_key_id,
            'condition_field' => $n->condition_field, 'condition_operator' => $n->condition_operator,
            'condition_value' => $n->condition_value, 'wait_seconds' => $n->wait_seconds,
        ])->values()),
        typeLabel(t) { return {message:'Pesan',condition:'Kondisi',ai:'AI',wait:'Tunggu'}[t] || t; },
        addNode(type) { this.nodes.push({ id: null, type, label: '', reply_message: '', ai_key_id: '', condition_field: '', condition_operator: 'equals', condition_value: '', wait_seconds: 5 }); },
        removeNode(i) { this.nodes.splice(i, 1); },
        prepareSubmit() {
            const box = document.getElementById('nodesPayload'); box.innerHTML = '';
            this.nodes.forEach((n, i) => {
                const fields = { ...n, sort_order: i };
                Object.entries(fields).forEach(([k, v]) => {
                    if (v === null || v === '') return;
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `nodes[${i}][${k}]`; inp.value = v;
                    box.appendChild(inp);
                });
            });
        }
    };
}
</script>
@endsection
