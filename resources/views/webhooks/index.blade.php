@extends('layouts.app')
@section('title', 'Webhook — WABot')
@section('content')

@php
$eventOptions = [
    'message.received'      => ['label' => 'Pesan Masuk', 'icon' => 'fa-inbox', 'cls' => 'bg-sky-50 text-sky-700 border-sky-200'],
    'message.sent'          => ['label' => 'Pesan Terkirim', 'icon' => 'fa-paper-plane', 'cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    'session.connected'     => ['label' => 'Sesi Terhubung', 'icon' => 'fa-plug', 'cls' => 'bg-green-50 text-green-700 border-green-200'],
    'session.disconnected'  => ['label' => 'Sesi Terputus', 'icon' => 'fa-plug-circle-xmark', 'cls' => 'bg-rose-50 text-rose-700 border-rose-200'],
    'campaign.completed'    => ['label' => 'Kampanye Selesai', 'icon' => 'fa-bullhorn', 'cls' => 'bg-amber-50 text-amber-700 border-amber-200'],
];
@endphp

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Webhook</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $webhooks->count() }} endpoint · kirim event WABot ke sistem lain secara real-time</p>
    </div>
    <button onclick="openCreate()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah Webhook
    </button>
</div>

<div class="grid gap-3 mb-8">
    @forelse($webhooks as $w)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 bg-violet-50">
                    <i class="fas fa-bolt text-violet-500"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                        <span class="font-semibold text-sm text-gray-900 truncate">{{ $w->name }}</span>
                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium {{ $w->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $w->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                            {{ $w->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @if($w->last_triggered_at)
                        <span class="text-[11px] text-gray-400"><i class="fas fa-clock-rotate-left mr-0.5"></i> {{ $w->last_triggered_at->diffForHumans() }}</span>
                        @endif
                    </div>
                    <div class="font-mono text-xs text-gray-700 bg-gray-50 px-2.5 py-1.5 rounded-lg break-all mb-2">{{ $w->url }}</div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(($w->events ?? []) as $ev)
                            @php $em = $eventOptions[$ev] ?? null; @endphp
                            <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-0.5 rounded-md border {{ $em['cls'] ?? 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                <i class="fas {{ $em['icon'] ?? 'fa-circle' }} text-[9px]"></i> {{ $em['label'] ?? $ev }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <form method="POST" action="{{ route('webhooks.test', $w) }}">
                    @csrf
                    <button class="p-1.5 rounded-lg hover:bg-sky-50 text-gray-400 hover:text-sky-600" title="Kirim test"><i class="fas fa-vial text-xs"></i></button>
                </form>
                <button onclick="editWebhook({{ $w->id }})"
                    class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('webhooks.destroy', $w) }}" onsubmit="return confirm('Hapus webhook ini?')">
                    @csrf @method('DELETE')
                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-bolt text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-500 font-medium mb-1">Belum ada webhook</p>
        <p class="text-sm text-gray-400 mb-4">Tambahkan endpoint untuk menerima notifikasi event secara otomatis</p>
        <button onclick="openCreate()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-plus text-xs"></i> Tambah Webhook
        </button>
    </div>
    @endforelse
</div>

{{-- Recent Logs --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2">
        <i class="fas fa-list-ul text-gray-400 text-sm"></i>
        <h2 class="text-sm font-bold text-gray-900">Log Terbaru</h2>
        <span class="text-xs text-gray-400">({{ $recentLogs->count() }} entri)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400 border-b border-gray-100">
                    <th class="px-4 py-2.5">Waktu</th>
                    <th class="px-4 py-2.5">Webhook</th>
                    <th class="px-4 py-2.5">Event</th>
                    <th class="px-4 py-2.5">Status</th>
                    <th class="px-4 py-2.5">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentLogs as $log)
                @php
                    $code = $log->response_code;
                    $ok = $code !== null && $code >= 200 && $code < 300;
                @endphp
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2.5 text-gray-500 whitespace-nowrap text-xs">{{ $log->created_at?->format('d M H:i:s') }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $log->webhook?->name ?? '—' }}</td>
                    <td class="px-4 py-2.5"><span class="font-mono text-xs text-gray-600">{{ $log->event }}</span></td>
                    <td class="px-4 py-2.5">
                        @if($code === null)
                            <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200"><i class="fas fa-xmark text-[9px]"></i> Gagal</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-mono font-medium px-2 py-0.5 rounded-md border {{ $ok ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <i class="fas {{ $ok ? 'fa-check' : 'fa-triangle-exclamation' }} text-[9px]"></i> {{ $code }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs max-w-xs truncate" title="{{ $log->error ?: $log->response_body }}">{{ $log->error ?: ($log->response_body ?: '—') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">
                        <i class="fas fa-inbox text-2xl mb-2 block opacity-40"></i> Belum ada log
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div id="whModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="whModalTitle">Tambah Webhook</h2>
        <form method="POST" action="{{ route('webhooks.store') }}" class="space-y-3" id="whForm">
            @csrf
            <div id="whMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">Nama</label>
                <input type="text" name="name" placeholder="CRM Integrasi" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">URL Endpoint</label>
                <input type="url" name="url" placeholder="https://app.contoh.com/webhook" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500 mb-1.5 block">Event yang dikirim</label>
                <div class="grid grid-cols-1 gap-1.5">
                    @foreach($eventOptions as $key => $opt)
                    <label class="flex items-center gap-2.5 px-3 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="events[]" value="{{ $key }}" class="wh-event rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <i class="fas {{ $opt['icon'] }} text-gray-400 text-xs w-4 text-center"></i>
                        <span class="text-sm text-gray-700">{{ $opt['label'] }}</span>
                        <span class="ml-auto font-mono text-[11px] text-gray-400">{{ $key }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Status</label>
                <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="closeModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

@php
$webhooksJson = $webhooks->keyBy('id')->map(function($w) {
    return ['name' => $w->name, 'url' => $w->url, 'events' => $w->events ?? [], 'is_active' => $w->is_active];
});
@endphp

@push('scripts')
<script>
const webhooksData = {!! json_encode($webhooksJson) !!};

function closeModal() {
    document.getElementById('whModal').classList.add('hidden');
}

function setEvents(events) {
    document.querySelectorAll('.wh-event').forEach(cb => {
        cb.checked = events.includes(cb.value);
    });
}

function openCreate() {
    const f = document.getElementById('whForm');
    document.getElementById('whModalTitle').textContent = 'Tambah Webhook';
    f.action = '{{ route('webhooks.store') }}';
    f.querySelector('input[name="name"]').value = '';
    f.querySelector('input[name="url"]').value = '';
    f.querySelector('select[name="is_active"]').value = '1';
    setEvents([]);
    document.getElementById('whMethodField').innerHTML = '';
    document.getElementById('whModal').classList.remove('hidden');
}

function editWebhook(id) {
    const data = webhooksData[id];
    if (!data) return;
    const f = document.getElementById('whForm');
    document.getElementById('whModalTitle').textContent = 'Edit Webhook';
    f.action = '/webhooks/' + id;
    f.querySelector('input[name="name"]').value = data.name;
    f.querySelector('input[name="url"]').value = data.url;
    f.querySelector('select[name="is_active"]').value = data.is_active ? '1' : '0';
    setEvents(data.events || []);
    document.getElementById('whMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('whModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
