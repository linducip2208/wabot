@extends('layouts.app')
@section('title', 'URL Shortener — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">URL Shortener</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola integrasi URL shortener untuk kampanye</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah Shortener
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Nama</th>
                <th class="px-5 py-3 hidden md:table-cell">Base URL</th>
                <th class="px-5 py-3 hidden lg:table-cell">Dibuat</th>
                <th class="px-5 py-3 w-24 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($shorteners as $s)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3 font-medium text-gray-900">{{ $s->name }}</td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell font-mono text-xs">{{ $s->base_url }}</td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $s->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <button onclick='editShortener({{ $s->id }}, {{ json_encode($s->name) }}, {{ json_encode($s->base_url) }})'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('admin.shorteners.destroy', $s) }}" class="inline" onsubmit="return confirm('Hapus shortener ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-16 text-center text-gray-500">Belum ada URL Shortener terdaftar.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="shortenerModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle">Tambah URL Shortener</h2>
        <form method="POST" action="{{ route('admin.shorteners.store') }}" class="space-y-3" id="shortenerForm">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">Nama</label>
                <input type="text" name="name" placeholder="contoh: Bitly" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Base URL</label>
                <input type="url" name="base_url" placeholder="https://api-ssl.bitly.com/v4" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">API Key</label>
                <input type="password" name="api_key" placeholder="Masukkan API key..." required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-[11px] text-gray-400 mt-0.5">Dienskripsi sebelum disimpan. Kosongkan saat edit jika tidak ingin mengubah.</p>
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
    const m = document.getElementById('shortenerModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('modalTitle').textContent = 'Tambah URL Shortener';
        const f = document.getElementById('shortenerForm');
        f.action = '{{ route('admin.shorteners.store') }}';
        f.reset();
        document.getElementById('methodField').innerHTML = '';
    }
}
function editShortener(id, name, baseUrl) {
    const m = document.getElementById('shortenerModal');
    m.classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit URL Shortener';
    const f = document.getElementById('shortenerForm');
    f.action = '/admin/shorteners/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="base_url"]').value = baseUrl;
    f.querySelector('input[name="api_key"]').value = '';
    f.querySelector('input[name="api_key"]').required = false;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
