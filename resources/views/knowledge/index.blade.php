@extends('layouts.app')

@section('title', 'Knowledge Base / FAQ')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Knowledge Base / FAQ</h1>
    <p class="text-sm text-gray-500 mb-6">Training data untuk AI auto-reply. AI akan menggunakan knowledge ini sebagai konteks menjawab.</p>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-800 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-800 rounded-xl px-4 py-3 mb-4 text-sm">{{ session('error') }}</div>
    @endif

    {{-- FORM: FAQ Manual --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">Tambah FAQ (Q&A Manual)</h2>
        <form method="POST" action="{{ route('knowledge.store') }}" id="faqForm">
            @csrf
            <input type="hidden" name="type" value="faq">
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500">Nama Knowledge</label>
                <input name="title" required placeholder="mis. FAQ Produk" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div id="faqRows">
                <div class="faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50">
                    <div class="grid grid-cols-12 gap-2">
                        <input name="faqs[0][category]" placeholder="Kategori (opsional)" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][question]" placeholder="Pertanyaan" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <input name="faqs[0][answer]" placeholder="Jawaban" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                        <button type="button" class="col-span-1 text-red-500 text-xs delete-row hidden">✕</button>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 mt-2">
                <button type="button" onclick="addRow()" class="text-xs bg-gray-100 border border-gray-300 rounded-lg px-3 py-1.5 hover:bg-gray-200">+ Tambah Baris</button>
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700">Simpan FAQ</button>
            </div>
        </form>
    </div>

    {{-- FORM: CSV Upload --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">Upload CSV Knowledge</h2>
        <form method="POST" action="{{ route('knowledge.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="text-xs font-medium text-gray-500">Nama Knowledge</label>
                <input name="title" required placeholder="mis. FAQ pandusolusi.com" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
            </div>
            <div class="flex gap-2 items-center">
                <input type="file" name="file" accept=".csv,.txt" required class="text-xs border border-gray-300 rounded-lg px-3 py-1.5 file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-brand-50 file:text-brand-700 file:rounded-lg file:text-xs">
                <button type="submit" class="text-xs bg-brand-600 text-white rounded-lg px-4 py-1.5 hover:bg-brand-700">Upload CSV</button>
            </div>
            <p class="text-xs text-gray-400 mt-2">Format CSV: kolom <code>question,answer</code> (atau <code>category,question,answer</code>). <a href="#" onclick="downloadSample()" class="text-brand-600 underline">Download contoh CSV</a></p>
        </form>
    </div>

    {{-- DAFTAR Knowledge --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <h2 class="font-semibold text-gray-800 mb-3">Daftar Knowledge ({{ $entries->count() }})</h2>
        @forelse($entries as $e)
            @php $rows = $e->rows; @endphp
            <div class="border border-gray-100 rounded-xl p-4 mb-3 bg-gray-50/30">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <span class="font-semibold text-sm">{{ $e->title }}</span>
                        <span class="text-xs text-gray-400 ml-2">{{ strtoupper($e->type) }} · {{ count($rows) }} item</span>
                        @if($e->is_active)
                            <span class="ml-2 text-xs text-green-700 bg-green-50 px-2 py-0.5 rounded-full">Aktif</span>
                        @else
                            <span class="ml-2 text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Nonaktif</span>
                        @endif
                    </div>
                    <div class="flex gap-1">
                        <form method="POST" action="{{ route('knowledge.toggle', $e) }}" class="inline">
                            @csrf
                            <button class="text-xs {{ $e->is_active ? 'text-amber-600' : 'text-green-600' }} bg-white border border-gray-200 rounded-lg px-2 py-1">{{ $e->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        </form>
                        <form method="POST" action="{{ route('knowledge.destroy', $e) }}" class="inline" onsubmit="return confirm('Hapus knowledge ini?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 bg-white border border-gray-200 rounded-lg px-2 py-1">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400">Belum ada knowledge. Tambah FAQ manual atau upload CSV.</p>
        @endforelse
    </div>
</div>

<script>
let faqIndex = 1;
function addRow() {
    const wrap = document.getElementById('faqRows');
    const row = document.createElement('div');
    row.className = 'faq-row border border-gray-200 rounded-xl p-3 mb-2 bg-gray-50/50';
    row.innerHTML = `<div class="grid grid-cols-12 gap-2">
        <input name="faqs[${faqIndex}][category]" placeholder="Kategori (opsional)" class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][question]" placeholder="Pertanyaan" required class="col-span-4 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <input name="faqs[${faqIndex}][answer]" placeholder="Jawaban" required class="col-span-3 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
        <button type="button" class="col-span-1 text-red-500 text-xs" onclick="this.parentElement.parentElement.remove()">✕</button>
    </div>`;
    wrap.appendChild(row);
    faqIndex++;
}
function downloadSample() {
    const csv = 'category,question,answer\nProduk,Apa produk yang dijual?,Kami menjual software dan plugin.\nPembayaran,Bagaimana cara bayar?,Transfer bank atau QRIS.\nSupport,Bagaimana cara dapat bantuan?,Hubungi admin di WA 08123456789.';
    const blob = new Blob([csv], {type: 'text/csv'});
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a'); a.href = url; a.download = 'knowledge-sample.csv'; a.click();
}
</script>
@endsection
