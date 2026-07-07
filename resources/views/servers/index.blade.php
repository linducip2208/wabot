@extends('layouts.app')
@section('title', 'Server — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Server</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola koneksi Baileys & pantau performa</p>
    </div>
    @if(Auth::user()->isAdmin())
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah Server
    </button>
    @endif
</div>

{{-- Stats Bar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-server text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total Server</div><div class="text-xl font-extrabold text-gray-900">{{ $servers->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Online</div><div class="text-xl font-extrabold text-gray-900">{{ $servers->filter(fn($s) => $s->is_active)->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-mobile-alt text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total Sesi</div><div class="text-xl font-extrabold text-gray-900">{{ $totalSessions }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-exchange-alt text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Pesan/Hari</div><div class="text-xl font-extrabold text-gray-900">{{ number_format($todayMessages) }}</div></div>
    </div>
</div>

@if($servers->count() > 0)
<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-bar text-brand-500"></i> Pesan per Server</h2>
        <canvas id="serverMsgChart" height="200"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-pie text-violet-500"></i> Sesi per Server</h2>
        <canvas id="serverSessionChart" height="200"></canvas>
    </div>
</div>
@endif

{{-- Server Cards --}}
<div class="grid gap-4">
    @forelse($servers as $s)
    @php
        $sessions = $s->sessions;
        $connected = $sessions->where('status', 'connected')->count();
        $totalSess = $sessions->count();
        $online = false;
        try { $online = app(\App\Services\BaileysService::class)->check($s); } catch(\Exception $e) {}
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden card-lift">
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl {{ $online ? 'bg-emerald-50' : 'bg-red-50' }} flex items-center justify-center">
                        <i class="fas fa-server text-xl {{ $online ? 'text-emerald-500' : 'text-red-400' }}"></i>
                    </div>
                    <div>
                        <div class="font-bold text-gray-900 text-lg">{{ $s->name }}</div>
                        <div class="text-sm text-gray-500 font-mono">{{ $s->host }}:{{ $s->port }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full {{ $online ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                        <span class="w-2 h-2 rounded-full {{ $online ? 'bg-emerald-500 animate-pulse' : 'bg-red-400' }}"></span>
                        {{ $online ? 'Online' : 'Offline' }}
                    </span>
                    @if(Auth::user()->isAdmin())
                    <button onclick="editServer({{ $s->id }}, '{{ $s->name }}', '{{ $s->host }}', {{ $s->port }}, '{{ $s->api_key }}')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-sm"></i></button>
                    <form method="POST" action="{{ route('servers.destroy', $s) }}" onsubmit="return confirm('Hapus server?')" class="inline">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-sm"></i></button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold {{ $connected > 0 ? 'text-emerald-600' : 'text-gray-400' }}">{{ $connected }}</div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Sesi Online</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700">{{ $totalSess }}</div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Total Sesi</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700">{{ $s->messages_count ?? 0 }}</div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Pesan Terproses</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-extrabold text-gray-700">{{ number_format($s->uptime ?? 0, 1) }}%</div>
                    <div class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-0.5">Uptime 24j</div>
                </div>
            </div>

            @if($totalSess > 0)
            <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2">Sesi Aktif</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($sessions->take(8) as $ses)
                    <a href="{{ route('sessions.show', $ses) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium transition
                        {{ $ses->status === 'connected' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $ses->status === 'connected' ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ $ses->name }}
                    </a>
                    @endforeach
                    @if($totalSess > 8) <span class="text-xs text-gray-400 self-center">+{{ $totalSess - 8 }} lagi</span> @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-server text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 mb-1 font-medium">Belum ada server</p>
        <p class="text-sm text-gray-400 mb-4">Tambah server Baileys untuk menghubungkan WhatsApp</p>
        <button onclick="toggleModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> Tambah Server
        </button>
    </div>
    @endforelse
</div>

@if(Auth::user()->isAdmin())
{{-- Add/Edit Modal --}}
<div id="serverModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4"><span id="modalTitle">Tambah</span> Server Baileys</h2>
        <form id="serverForm" method="POST" action="{{ route('servers.store') }}" class="space-y-3">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">Nama Server</label>
                <input type="text" name="name" placeholder="VPS Jakarta" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Host</label>
                <input type="text" name="host" placeholder="192.168.1.1 atau domain" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Port</label>
                <input type="number" name="port" value="3100" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">API Key</label>
                <input type="text" name="api_key" placeholder="wabot-secret-key" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() { document.getElementById('serverModal').classList.toggle('hidden'); }
function editServer(id, name, host, port, apiKey) {
    document.getElementById('serverModal').classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit';
    const f = document.getElementById('serverForm');
    f.action = '/servers/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="host"]').value = host;
    f.querySelector('input[name="port"]').value = port;
    f.querySelector('input[name="api_key"]').value = apiKey;
    let m = document.getElementById('methodField');
    if (!m.querySelector('input')) m.innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endif
@endsection

@push('scripts')
@if($servers->count() > 0)
<script>
new Chart(document.getElementById('serverMsgChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($serverLabels) !!},
        datasets: [{ label: 'Pesan', data: {!! json_encode($serverMessages) !!}, backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'], borderRadius: 8 }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } }, x: { ticks: { font: { size: 10 } }, grid: { display: false } } }
    }
});
new Chart(document.getElementById('serverSessionChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($serverLabels) !!},
        datasets: [{ data: {!! json_encode($serverSessions) !!}, backgroundColor: ['#3b82f6','#8b5cf6','#06b6d4','#10b981','#f59e0b','#ef4444'], borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16, font: { size: 11 } } } }
    }
});
</script>
@endif
@endpush
