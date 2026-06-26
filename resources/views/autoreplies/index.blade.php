@extends('layouts.app')
@section('title', 'Auto-Reply — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Auto-Reply</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $autoreplies->count() }} rule · balas otomatis berdasarkan keyword</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah Rule
    </button>
</div>

<div class="grid gap-3">
    @forelse($autoreplies as $a)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0
                    {{ $a->match_type === 'exact' ? 'bg-rose-50' : ($a->match_type === 'contains' ? 'bg-sky-50' : 'bg-amber-50') }}">
                    <i class="fas {{ $a->match_type === 'exact' ? 'fa-equals text-rose-500' : ($a->match_type === 'contains' ? 'fa-search text-sky-500' : 'fa-arrow-right text-amber-500') }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono px-2 py-0.5 rounded-md
                            {{ $a->match_type === 'exact' ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($a->match_type === 'contains' ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                            {{ ['exact'=>'Persis','contains'=>'Mengandung','starts_with'=>'Diawali'][$a->match_type] }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $a->session?->name ?? 'Semua sesi' }}</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $a->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $a->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $a->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-gray-500 mb-0.5">Keyword</div>
                            <div class="font-mono text-sm text-gray-900 bg-gray-50 px-2.5 py-1 rounded-lg break-all">{{ $a->keyword }}</div>
                        </div>
                        <div class="text-gray-300 flex-shrink-0 self-stretch flex items-center"><i class="fas fa-arrow-right text-sm"></i></div>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-gray-500 mb-0.5">Balasan</div>
                            <div class="text-sm text-gray-700 bg-green-50/50 px-2.5 py-1 rounded-lg break-all line-clamp-2">{{ $a->reply_message }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button onclick='editRule({{ $a->id }}, "{{ addslashes($a->keyword) }}", "{{ addslashes($a->reply_message) }}", "{{ $a->match_type }}", {{ $a->session_id ?? 'null' }}, {{ $a->is_active ? 'true' : 'false' }})'
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('autoreplies.destroy', $a) }}" onsubmit="return confirm('Hapus rule?')">
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
        <p class="text-gray-500 font-medium mb-1">Belum ada rule auto-reply</p>
        <p class="text-sm text-gray-400 mb-4">Balas otomatis berdasarkan keyword yang cocok</p>
        <button onclick="toggleModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> Tambah Rule
        </button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="ruleModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="ruleModalTitle">Tambah Auto-Reply</h2>
        <form method="POST" action="{{ route('autoreplies.store') }}" class="space-y-3" id="ruleForm">
            @csrf
            <div id="ruleMethodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Keyword</label>
                    <input type="text" name="keyword" placeholder="hi" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Tipe Match</label>
                    <select name="match_type" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="contains">Mengandung</option>
                        <option value="exact">Persis</option>
                        <option value="starts_with">Diawali</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Balasan <span class="text-gray-400">({'{Halo|Hai}'} = spintax)</span></label>
                <textarea name="reply_message" rows="3" required placeholder="Halo! {'{Ada yang bisa dibantu?|Silakan kirim pesan}'}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Sesi (kosong = semua)</label>
                    <select name="session_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="">Semua sesi</option>
                        @foreach($sessions as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Status</label>
                    <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
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
    const m = document.getElementById('ruleModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('ruleModalTitle').textContent = 'Tambah Auto-Reply';
        document.getElementById('ruleForm').action = '{{ route('autoreplies.store') }}';
        document.getElementById('ruleForm').querySelector('input[name="keyword"]').value = '';
        document.getElementById('ruleForm').querySelector('textarea[name="reply_message"]').value = '';
        document.getElementById('ruleForm').querySelector('select[name="match_type"]').value = 'contains';
        document.getElementById('ruleForm').querySelector('select[name="session_id"]').value = '';
        document.getElementById('ruleForm').querySelector('select[name="is_active"]').value = '1';
        document.getElementById('ruleMethodField').innerHTML = '';
    }
}
function editRule(id, keyword, reply, matchType, sessionId, isActive) {
    const m = document.getElementById('ruleModal');
    m.classList.remove('hidden');
    document.getElementById('ruleModalTitle').textContent = 'Edit Auto-Reply';
    const f = document.getElementById('ruleForm');
    f.action = '/autoreplies/' + id;
    f.querySelector('input[name="keyword"]').value = keyword;
    f.querySelector('textarea[name="reply_message"]').value = reply;
    f.querySelector('select[name="match_type"]').value = matchType;
    f.querySelector('select[name="session_id"]').value = sessionId || '';
    f.querySelector('select[name="is_active"]').value = isActive ? '1' : '0';
    document.getElementById('ruleMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
