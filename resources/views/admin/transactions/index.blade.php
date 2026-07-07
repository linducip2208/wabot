@extends('layouts.app')
@section('title', 'Transaksi — Admin')
@section('content')

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Transaksi Pembayaran</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $transactions->count() }} transaksi tercatat</p>
    </div>
</div>

{{-- Stat Bar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    @php
        $totalAmount = $transactions->where('status', 'completed')->sum('amount');
        $pending = $transactions->where('status', 'pending')->count();
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-exchange-alt text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total</div><div class="text-xl font-extrabold text-gray-900">{{ $transactions->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Berhasil</div><div class="text-xl font-extrabold text-gray-900">Rp {{ number_format($totalAmount, 0, ',', '.') }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Pending</div><div class="text-xl font-extrabold text-gray-900">{{ $pending }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-times-circle text-red-400"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Gagal</div><div class="text-xl font-extrabold text-gray-900">{{ $transactions->where('status', 'failed')->count() }}</div></div>
    </div>
</div>

{{-- Filters --}}
<div class="flex items-center gap-3 mb-4 flex-wrap">
    <form method="GET" class="flex items-center gap-3 flex-wrap w-full">
        <select name="type" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            @foreach($types as $t)
                <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-gray-300 px-3 py-2 text-xs font-medium text-gray-600 focus:ring-2 focus:ring-brand-500" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            @foreach($statuses as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        @if(request('type') || request('status'))
            <a href="{{ route('admin.transactions.index') }}" class="text-xs text-brand-600 hover:underline py-2">Reset Filter</a>
        @endif
    </form>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3 hidden md:table-cell">Paket</th>
                <th class="px-5 py-3">Tipe</th>
                <th class="px-5 py-3">Jumlah</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 hidden lg:table-cell">Tanggal</th>
                <th class="px-5 py-3 w-28 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($transactions as $trx)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <span class="font-mono text-xs text-gray-500">#{{ $trx->id }}</span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-[10px] font-bold" style="background: {{ collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($trx->user?->name ?? '') % 6) }}">
                            {{ strtoupper(substr($trx->user?->name ?? '?', 0, 2)) }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 text-xs">{{ $trx->user?->name ?? 'N/A' }}</div>
                            <div class="text-[11px] text-gray-400">{{ $trx->user?->email ?? '' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="text-xs font-medium text-gray-600">{{ $trx->subscription?->plan?->name ?? '-' }}</span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">{{ ucfirst($trx->type) }}</span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($trx->amount, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full
                        {{ $trx->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $trx->status === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}
                        {{ $trx->status === 'failed' ? 'bg-red-50 text-red-600' : '' }}">
                        {{ ucfirst($trx->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $trx->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right">
                    @if($trx->status === 'pending')
                    <div class="flex items-center justify-end gap-1">
                        <form method="POST" action="{{ route('admin.transactions.update', $trx) }}" class="inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600" title="Tandai Selesai">
                                <i class="fas fa-check text-xs"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.transactions.update', $trx) }}" class="inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="failed">
                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="Tandai Gagal">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </form>
                    </div>
                    @else
                    <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-16 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-exchange-alt text-xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada transaksi</p>
                    <p class="text-sm text-gray-400">Transaksi akan muncul setelah pengguna melakukan pembayaran</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
