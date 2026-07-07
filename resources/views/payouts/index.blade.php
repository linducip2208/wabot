@extends('layouts.app')
@section('title', 'Payout — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Payout</h1>
        <p class="text-sm text-gray-500 mt-0.5">Ajukan pencairan saldo ke PayPal atau transfer bank</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Ajukan Payout
    </button>
</div>

{{-- Stat Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-file-invoice text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total</div><div class="text-xl font-extrabold text-gray-900">{{ $payouts->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-clock text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Pending</div><div class="text-xl font-extrabold text-gray-900">{{ $payouts->where('status','pending')->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-check-circle text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Selesai</div><div class="text-xl font-extrabold text-gray-900">{{ $payouts->where('status','completed')->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-times-circle text-red-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Ditolak</div><div class="text-xl font-extrabold text-gray-900">{{ $payouts->where('status','rejected')->count() }}</div></div>
    </div>
</div>

{{-- Payout Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">ID</th>
                <th class="px-5 py-3">Jumlah</th>
                <th class="px-5 py-3">Metode</th>
                <th class="px-5 py-3 hidden md:table-cell">Info Akun</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 hidden lg:table-cell">Diajukan</th>
                <th class="px-5 py-3 hidden lg:table-cell">Diproses</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($payouts as $p)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3 text-gray-500 text-xs font-mono">#{{ $p->id }}</td>
                <td class="px-5 py-3 font-semibold text-gray-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $p->method === 'paypal' ? 'bg-sky-50 text-sky-700' : 'bg-violet-50 text-violet-700' }}">
                        {{ $p->method === 'paypal' ? 'PayPal' : 'Bank Transfer' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell max-w-[180px] truncate">{{ $p->account_info }}</td>
                <td class="px-5 py-3">
                    @if($p->status === 'pending')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Pending</span>
                    @elseif($p->status === 'completed')
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">Selesai</span>
                    @else
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-50 text-red-700">Ditolak</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $p->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-xs text-gray-400 hidden lg:table-cell">{{ $p->processed_at ? $p->processed_at->format('d M Y H:i') : '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-16 text-center text-gray-500">Belum ada permintaan payout</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Create Modal --}}
<div id="payoutModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Ajukan Payout</h2>
        <form method="POST" action="{{ route('payouts.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">Jumlah (Rp)</label>
                <input type="number" name="amount" min="10000" step="1000" placeholder="50000" required
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Metode</label>
                <select name="method" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="paypal">PayPal</option>
                    <option value="bank_transfer">Bank Transfer</option>
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Info Akun</label>
                <textarea name="account_info" rows="2" required placeholder="Email PayPal atau no.rek & nama bank"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Ajukan</button>
            </div>
        </form>
    </div>
</div>

<script>function toggleModal(){document.getElementById('payoutModal').classList.toggle('hidden');}</script>
@endsection
