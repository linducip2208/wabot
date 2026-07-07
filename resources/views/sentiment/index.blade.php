@extends('layouts.app')
@section('title', 'Sentiment — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Sentiment Analysis</h1>
        <p class="text-sm text-gray-500 mt-0.5">Analisis emosi pesan pelanggan secara otomatis</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
    @foreach(['Hari Ini'=>$statsToday, 'Minggu Ini'=>$statsWeek, 'Bulan Ini'=>$statsMonth] as $label => $s)
    <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $label }}</span>
            <span class="text-xs text-gray-400">{{ $s['total'] ?? 0 }} pesan</span>
        </div>
        <div class="space-y-2">
            <div>
                <div class="flex items-center justify-between text-xs mb-1"><span class="text-emerald-600"><i class="fas fa-smile mr-1"></i> Positif</span><span class="font-semibold">{{ $s['positive'] ?? 0 }}%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-emerald-500" style="width: {{ $s['positive'] ?? 0 }}%"></div></div>
            </div>
            <div>
                <div class="flex items-center justify-between text-xs mb-1"><span class="text-gray-500"><i class="fas fa-meh mr-1"></i> Netral</span><span class="font-semibold">{{ $s['neutral'] ?? 0 }}%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-gray-400" style="width: {{ $s['neutral'] ?? 0 }}%"></div></div>
            </div>
            <div>
                <div class="flex items-center justify-between text-xs mb-1"><span class="text-red-600"><i class="fas fa-frown mr-1"></i> Negatif</span><span class="font-semibold">{{ $s['negative'] ?? 0 }}%</span></div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden"><div class="h-full bg-red-500" style="width: {{ $s['negative'] ?? 0 }}%"></div></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="grid lg:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4">Distribusi Sentiment</h2>
        <canvas id="distChart" height="220"></canvas>
    </div>
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4">Tren 14 Hari</h2>
        <canvas id="trendChart" height="120"></canvas>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden mt-4">
    <div class="px-5 py-3 border-b border-gray-100 font-semibold text-gray-800">Log Terbaru</div>
    <table class="w-full text-sm">
        <thead><tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase"><th class="px-5 py-2">Kontak</th><th class="px-5 py-2">Pesan</th><th class="px-5 py-2">Sentiment</th><th class="px-5 py-2">Confidence</th><th class="px-5 py-2">Waktu</th></tr></thead>
        <tbody class="divide-y divide-gray-100">
            @php $sm = ['positive'=>['Positif','bg-emerald-50 text-emerald-700'],'neutral'=>['Netral','bg-gray-100 text-gray-600'],'negative'=>['Negatif','bg-red-50 text-red-700']]; @endphp
            @forelse($recentLogs as $log)
            @php $b = $sm[$log->sentiment] ?? [$log->sentiment,'bg-gray-100 text-gray-600']; @endphp
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-2.5 text-gray-800">{{ $log->contact?->name ?? '-' }}</td>
                <td class="px-5 py-2.5 text-gray-600 max-w-xs truncate">{{ $log->message_text }}</td>
                <td class="px-5 py-2.5"><span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium {{ $b[1] }}">{{ $b[0] }}</span></td>
                <td class="px-5 py-2.5 text-gray-500">{{ round(($log->confidence ?? 0) * 100) }}%</td>
                <td class="px-5 py-2.5 text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400"><i class="fas fa-smile text-3xl mb-2"></i><p>Belum ada log sentiment</p></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const cd = @json($chartData);
    const td = @json($trendChart);
    if (window.Chart) {
        new Chart(document.getElementById('distChart'), {
            type: 'doughnut',
            data: { labels: cd.labels, datasets: [{ data: cd.values, backgroundColor: ['#10b981','#9ca3af','#ef4444'] }] },
            options: { plugins: { legend: { position: 'bottom' } } }
        });
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: { labels: td.labels, datasets: [
                { label: 'Positif', data: td.positive, borderColor: '#10b981', tension: .3 },
                { label: 'Netral', data: td.neutral, borderColor: '#9ca3af', tension: .3 },
                { label: 'Negatif', data: td.negative, borderColor: '#ef4444', tension: .3 },
            ]},
            options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
        });
    }
});
</script>
@endsection
