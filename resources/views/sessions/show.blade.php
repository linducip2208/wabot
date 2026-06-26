@extends('layouts.app')
@section('title', $session->name . ' — WABot')
@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('sessions.index') }}" class="text-sm text-gray-500 hover:text-brand-600">&larr; Kembali</a>
        <h1 class="text-2xl font-extrabold text-gray-900 mt-1">{{ $session->name }}</h1>
        <div class="text-sm text-gray-500 mt-1">
            Status:
            <span class="font-semibold {{ $session->status === 'connected' ? 'text-green-600' : 'text-yellow-600' }}">
                {{ $session->status === 'qr_ready' ? 'Menunggu Scan' : $session->status }}
            </span>
            @if($session->phone)
                &middot; <span class="font-mono">{{ $session->phone }}</span>
            @endif
    </div>
</div>

@if($logs->count() > 0)
<div class="mt-6 bg-white rounded-xl border border-gray-200 p-5">
    <h3 class="font-bold text-gray-900 mb-4 text-lg flex items-center gap-2"><i class="fas fa-history text-brand-500"></i> Riwayat Uptime</h3>
    <div class="space-y-2">
        @foreach($logs as $log)
        <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-50 transition text-sm">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0
                {{ $log->event === 'connected' ? 'bg-emerald-500' : '' }}
                {{ $log->event === 'disconnected' ? 'bg-red-500' : '' }}
                {{ $log->event === 'logged_out' ? 'bg-amber-500' : '' }}
                {{ $log->event === 'reconnecting' ? 'bg-blue-500 animate-pulse' : '' }}"></span>
            <div class="flex-1">
                <span class="font-medium capitalize">{{ str_replace('_', ' ', $log->event) }}</span>
                @if($log->phone) <span class="text-gray-400 font-mono text-xs ml-2">{{ preg_replace('/[@:].*$/', '', $log->phone) }}</span> @endif
                @if($log->reason) <span class="text-gray-400 text-xs ml-1">· {{ $log->reason }}</span> @endif
            </div>
            <span class="text-xs text-gray-400">{{ $log->logged_at->format('d M Y H:i:s') }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
    <div class="flex gap-2">
        <form method="POST" action="{{ route('sessions.destroy', $session) }}" onsubmit="return confirm('Hapus sesi ini? Semua data akan hilang.')">
            @csrf @method('DELETE')
            <button class="text-sm text-red-500 hover:underline">Hapus Sesi</button>
        </form>
    </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
        <h3 class="font-bold text-gray-900 mb-4 text-lg">
            @if($session->status === 'connected')
                ✅ WhatsApp Connected
            @else
                Scan QR Code
            @endif
        </h3>

        @if($session->status === 'connected')
            <div class="py-8">
                <div class="text-6xl mb-4">✅</div>
                <p class="text-xl font-bold text-green-600">Connected!</p>
                @if($session->phone)
                    <p class="text-lg text-gray-700 mt-2 font-mono">{{ $session->phone }}</p>
                @endif
                <p class="text-sm text-gray-400 mt-4">WhatsApp siap digunakan untuk auto-reply & kampanye.</p>
            </div>
        @elseif($qrImage)
            <div class="inline-block p-4 border border-gray-200 rounded-xl mb-4 bg-white">
                <img src="{{ $qrImage }}" alt="QR Code" class="w-72 h-72">
            </div>
            <p class="text-sm text-gray-500 font-medium">Buka WhatsApp &gt; <strong>Perangkat Tertaut</strong> &gt; Scan QR</p>
            <p class="text-xs text-gray-400 mt-2">Halaman refresh otomatis tiap 5 detik.</p>
        @else
            <div class="py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-600 mx-auto"></div>
                <p class="text-sm text-gray-500 mt-4">Menghubungkan ke WhatsApp...</p>
                <p class="text-xs text-gray-400 mt-2">Halaman refresh otomatis.</p>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h3 class="font-bold text-gray-900 mb-4 text-lg">Informasi Sesi</h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Nama</dt>
                <dd class="text-gray-900 font-medium">{{ $session->name }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Status</dt>
                <dd class="text-gray-900 font-medium capitalize">{{ $session->status === 'qr_ready' ? 'Menunggu Scan' : $session->status }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Nomor</dt>
                <dd class="text-gray-900 font-medium font-mono">{{ $session->phone ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Session ID</dt>
                <dd class="text-gray-900 font-medium font-mono text-xs">{{ $session->session_id }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Server</dt>
                <dd class="text-gray-900 font-medium">{{ $session->server?->name ?? '-' }}</dd>
            </div>
            <div class="flex justify-between py-1.5 border-b border-gray-100">
                <dt class="text-gray-500">Dibuat</dt>
                <dd class="text-gray-900 font-medium">{{ $session->created_at->format('d M Y H:i') }}</dd>
            </div>
        </dl>

        @if($session->status === 'connected')
            <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                <p class="text-sm text-green-800 font-medium">Sesi ini sudah aktif. Auto-reply dan kampanye sudah bisa digunakan.</p>
                <div class="flex gap-3 mt-3">
                    <a href="{{ route('autoreplies.index') }}" class="text-sm text-brand-600 font-semibold hover:underline">Atur Auto-Reply &rarr;</a>
                    <a href="{{ route('campaigns.create') }}" class="text-sm text-brand-600 font-semibold hover:underline">Buat Kampanye &rarr;</a>
                </div>
            </div>
        @endif
    </div>
</div>

@if($refresh)
<meta http-equiv="refresh" content="5">
@endif

@endsection
