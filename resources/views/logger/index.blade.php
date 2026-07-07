@extends('layouts.app')
@section('title', 'Log Aktivitas — WABot')
@section('content')

<div class="mb-5">
    <h1 class="text-xl font-extrabold text-gray-900">Log Aktivitas</h1>
    <p class="text-sm text-gray-500 mt-0.5">Rekam jejak aktivitas sesi WhatsApp dan webhook</p>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-list-alt text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total Log</div><div class="text-xl font-extrabold text-gray-900">{{ $logs->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-mobile-alt text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Sesi</div><div class="text-xl font-extrabold text-gray-900">{{ $logs->where('type','session')->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-plug text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Webhook</div><div class="text-xl font-extrabold text-gray-900">{{ $logs->where('type','webhook')->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Hari Ini</div><div class="text-xl font-extrabold text-gray-900">{{ $logs->where(fn($l) => \Carbon\Carbon::parse($l['created_at'])->isToday())->count() }}</div></div>
    </div>
</div>

{{-- Log Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">Tipe</th>
                <th class="px-5 py-3">Event</th>
                <th class="px-5 py-3 hidden md:table-cell">Detail</th>
                <th class="px-5 py-3 hidden lg:table-cell">Waktu</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    @if($log['type'] === 'session')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700"><i class="fas fa-mobile-alt mr-1"></i>Sesi</span>
                    @else
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-violet-50 text-violet-700"><i class="fas fa-plug mr-1"></i>Webhook</span>
                    @endif
                </td>
                <td class="px-5 py-3">
                    <span class="font-medium text-gray-900 text-xs">{{ $log['event'] }}</span>
                </td>
                <td class="px-5 py-3 text-gray-600 text-xs hidden md:table-cell max-w-[320px] truncate">{{ $log['detail'] }}</td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">
                    {{ \Carbon\Carbon::parse($log['created_at'])->translatedFormat('d M Y H:i') }}
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-16 text-center text-gray-500">Belum ada log aktivitas</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($logs->count() > 50)
<div class="text-center mt-3 text-xs text-gray-400">Menampilkan {{ $logs->count() }} log terbaru</div>
@endif

@endsection
