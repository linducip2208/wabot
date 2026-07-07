@extends('layouts.app')
@section('title', 'Payment Gateway — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Payment Gateway</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $gateways->count() }} gateway tersedia</p>
    </div>
    <button onclick="openCreate()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah Gateway
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($gateways as $g)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift {{ $g->is_active ? '' : 'opacity-50' }}">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background: {{ $g->logo_color ?? '#3b82f6' }}">
                    {{ substr($g->name, 0, 2) }}
                </div>
                <span class="font-semibold text-sm text-gray-900">{{ $g->name }}</span>
            </div>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium {{ $g->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $g->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
        <div class="text-xs text-gray-500 space-y-0.5 mb-3">
            @if($g->account_number)<div><span class="text-gray-400">Rek:</span> {{ $g->account_number }}</div>@endif
            @if($g->account_holder)<div><span class="text-gray-400">Nama:</span> {{ $g->account_holder }}</div>@endif
        </div>
        <div class="text-[11px] text-gray-400 bg-gray-50 rounded-lg p-2 max-h-20 overflow-y-auto whitespace-pre-line mb-3">{{ \Str::limit($g->instructions, 80) }}</div>
        <div class="flex gap-1">
            <button onclick="editGateway({{ $g->id }}, {{ json_encode($g->name) }}, '{{ $g->code }}', {{ json_encode($g->account_number) }}, {{ json_encode($g->account_holder) }}, {{ json_encode($g->instructions) }}, '{{ $g->logo_color }}', {{ $g->sort_order }})"
                class="flex-1 text-[11px] bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg py-1.5 font-medium text-center">Edit</button>
            <form method="POST" action="{{ route('admin.gateways.destroy', $g) }}" class="flex-1" onsubmit="return confirm('Hapus?')">
                @csrf @method('DELETE')
                <button class="w-full text-[11px] bg-red-50 text-red-600 hover:bg-red-100 rounded-lg py-1.5 font-medium">Hapus</button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Modal --}}
<div id="gwModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="gwModalTitle">Tambah Gateway</h2>
        <form method="POST" action="{{ route('admin.gateways.store') }}" class="space-y-3" id="gwForm">
            @csrf
            <div id="gwMethodField"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Kode</label>
                    <input type="text" name="code" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">No. Rekening / Tujuan</label>
                    <input type="text" name="account_number" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Nama Pemilik</label>
                    <input type="text" name="account_holder" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Instruksi Pembayaran <span class="text-gray-400">({'{no_rek}'}, {'{nama}'} = variable)</span></label>
                <textarea name="instructions" rows="4" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">Warna</label>
                    <input type="color" name="logo_color" value="#3b82f6" class="w-full h-10 rounded-xl border border-gray-300 px-2 py-1">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Urutan</label>
                    <input type="number" name="sort_order" value="0" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('gwModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openCreate() {
    document.getElementById('gwModalTitle').textContent = 'Tambah Gateway';
    const f = document.getElementById('gwForm');
    f.action = '{{ route('admin.gateways.store') }}';
    f.reset();
    document.getElementById('gwMethodField').innerHTML = '';
    document.getElementById('gwModal').classList.remove('hidden');
}
function editGateway(id, name, code, accNum, accHolder, instructions, color, order) {
    document.getElementById('gwModalTitle').textContent = 'Edit Gateway';
    const f = document.getElementById('gwForm');
    f.action = '/admin/gateways/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="code"]').value = code;
    f.querySelector('input[name="account_number"]').value = accNum || '';
    f.querySelector('input[name="account_holder"]').value = accHolder || '';
    f.querySelector('textarea[name="instructions"]').value = instructions || '';
    f.querySelector('input[name="logo_color"]').value = color || '#3b82f6';
    f.querySelector('input[name="sort_order"]').value = order || 0;
    document.getElementById('gwMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('gwModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
