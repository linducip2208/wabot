@extends('layouts.app')
@section('title', 'Antrian — WABot')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Antrian Pesan</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $messages->total() }} pesan mengantri</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('messages.received') }}" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-inbox mr-1"></i> Masuk
        </a>
        <a href="{{ route('messages.sent') }}" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-paper-plane mr-1"></i> Terkirim
        </a>
        <a href="{{ route('messages.queue') }}" class="bg-amber-600 text-white px-3 py-2 rounded-xl text-sm font-medium">
            <i class="fas fa-clock mr-1"></i> Antrian
        </a>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-3 py-2.5">Penerima</th>
                <th class="px-3 py-2.5 hidden md:table-cell">Pengirim (WA)</th>
                <th class="px-3 py-2.5 hidden md:table-cell">Pesan</th>
                <th class="px-3 py-2.5">Status</th>
                <th class="px-3 py-2.5 hidden lg:table-cell">Waktu</th>
                <th class="px-3 py-2.5 w-20 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($messages as $m)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-3 py-2.5">
                    <div class="font-medium text-gray-900 text-xs">{{ $m->contact?->name ?? preg_replace('/@.*$/', '', $m->phone) }}</div>
                    <div class="text-[11px] text-gray-400 font-mono">{{ preg_replace('/@.*$/', '', $m->phone) }}</div>
                </td>
                <td class="px-3 py-2.5 hidden md:table-cell">
                    <div class="font-medium text-gray-900 text-xs">{{ $m->session?->name ?? '-' }}</div>
                    <div class="text-[11px] text-gray-400 font-mono">{{ $m->session?->phone ?? '-' }}</div>
                </td>
                <td class="px-3 py-2.5 hidden md:table-cell text-gray-600 max-w-xs truncate">{{ \Str::limit($m->message, 60) }}</td>
                <td class="px-3 py-2.5">
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                        {{ $m->status === 'sending' ? 'bg-blue-50 text-blue-700' : '' }}
                        {{ $m->status === 'queued' ? 'bg-purple-50 text-purple-700' : '' }}
                        {{ $m->status === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}">
                        {{ ['pending'=>'Pending','queued'=>'Antri','sending'=>'Mengirim'][$m->status] ?? $m->status }}
                    </span>
                </td>
                <td class="px-3 py-2.5 hidden lg:table-cell text-xs text-gray-400">{{ $m->created_at->format('d M H:i') }}</td>
                <td class="px-3 py-2.5 text-right">
                    <form method="POST" action="{{ route('messages.resend', $m) }}" class="inline">
                        @csrf
                        <button class="p-1 rounded hover:bg-amber-50 text-gray-400 hover:text-amber-600 text-xs" title="Kirim ulang"><i class="fas fa-redo"></i></button>
                    </form>
                    <form method="POST" action="{{ route('messages.destroy', $m) }}" class="inline" onsubmit="return confirm('Hapus?')">
                        @csrf @method('DELETE')
                        <button class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-16 text-center text-gray-500">Tidak ada pesan dalam antrian.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $messages->links() }}</div>

@endsection
