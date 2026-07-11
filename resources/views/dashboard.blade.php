@extends('layouts.app')
@section('title', 'Dashboard — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('dash.welcome') }}, {{ Auth::user()->name }}</p>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
    @php
    $statCards = [
        ['session', $stats['sessions'], 'bg-blue-50 text-blue-500', 'fas fa-mobile-alt'],
        ['online', $stats['sessions_connected'], 'bg-emerald-50 text-emerald-500', 'fas fa-wifi'],
        ['contact', $stats['contacts'], 'bg-violet-50 text-violet-500', 'fas fa-address-book'],
        ['campaign', $stats['campaigns'], 'bg-amber-50 text-amber-500', 'fas fa-bullhorn'],
        ['in', $stats['messages_in'], 'bg-cyan-50 text-cyan-500', 'fas fa-inbox'],
        ['out', $stats['messages_out'], 'bg-rose-50 text-rose-500', 'fas fa-paper-plane'],
    ];
    @endphp
    @foreach($statCards as [$label, $val, $colorClass, $icon])
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg {{ $colorClass }} flex items-center justify-center flex-shrink-0">
            <i class="{{ $icon }}"></i>
        </div>
        <div>
            <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">{{ __('common.' . $label) }}</div>
            <div class="text-xl font-extrabold text-gray-900">{{ number_format($val) }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts --}}
<div class="grid lg:grid-cols-2 gap-5 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-bar text-brand-500"></i> {{ __('dash.weekly_activity') }}</h2>
        <canvas id="weeklyChart" height="220"></canvas>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-chart-line text-emerald-500"></i> {{ __('dash.today_activity') }}</h2>
        <canvas id="hourlyChart" height="220"></canvas>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-mobile-alt text-brand-500"></i> {{ __('common.session') }} WhatsApp</h2>
        <div class="space-y-2">
            @forelse($sessions as $s)
                <a href="{{ route('sessions.show', $s) }}" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $s->status === 'connected' ? 'bg-emerald-500' : ($s->status === 'qr_ready' ? 'bg-amber-500' : 'bg-gray-400') }} {{ $s->status === 'connected' ? 'animate-pulse' : '' }}"></span>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">{{ $s->name }}</div>
                            <div class="text-xs text-gray-500">{{ $s->phone ?? '-' }}</div>
                        </div>
                    </div>
                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $s->status === 'connected' ? 'bg-emerald-50 text-emerald-700' : ($s->status === 'qr_ready' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ str_replace('_',' ',$s->status) }}
                    </span>
                </a>
            @empty
                <p class="text-sm text-gray-500 py-4 text-center">{{ __('common.no') }} {{ __('common.session') }}.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-clock text-brand-500"></i> {{ __('dash.recent_messages') }}</h2>
        <div class="space-y-2">
            @forelse($recentMessages as $msg)
                <div class="flex items-start gap-3 py-2.5 px-3 rounded-lg hover:bg-gray-50 transition">
                    <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded {{ $msg->direction === 'in' ? 'bg-cyan-100 text-cyan-700' : 'bg-emerald-100 text-emerald-700' }} mt-0.5">
                        {{ $msg->direction === 'in' ? 'IN' : 'OUT' }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold text-gray-700">{{ preg_replace('/@.*$/', '', $msg->phone) }}</span>
                            <span class="text-[10px] text-gray-400">{{ $msg->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-600 truncate">{{ $msg->message }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 py-4 text-center">{{ __('common.no') }} {{ __('common.message') }}.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
new Chart(weeklyCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartData['labels']) !!},
        datasets: [
            { label: '{{ __('common.in') }}', data: {!! json_encode($chartData['in']) !!}, backgroundColor: '#06b6d4', borderRadius: 6, borderSkipped: false },
            { label: '{{ __('common.out') }}', data: {!! json_encode($chartData['out']) !!}, backgroundColor: '#10b981', borderRadius: 6, borderSkipped: false },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 11 } } } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { font: { size: 10 } }, grid: { display: false } }
        }
    }
});

const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
new Chart(hourlyCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($chartData['hourlyLabels']) !!},
        datasets: [
            { label: '{{ __('common.message') }}', data: {!! json_encode($chartData['hourlyData']) !!}, borderColor: '#8b5cf6', backgroundColor: 'rgba(139,92,246,0.08)', fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 5 }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { font: { size: 10 } }, grid: { color: '#f1f5f9' } },
            x: { ticks: { font: { size: 10 }, maxTicksLimit: 8 }, grid: { display: false } }
        }
    }
});
</script>
@endpush
