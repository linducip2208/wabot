@extends('layouts.app')
@section('title', 'Team Inbox — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Team Inbox</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $assignments->count() }} percakapan aktif ditugaskan ke tim</p>
    </div>
    <button onclick="document.getElementById('assignModal').classList.remove('hidden')" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-user-plus text-xs"></i> Tugaskan
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @forelse($assignments as $a)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($a->contact?->name ?? 'NA', 0, 2)) }}</div>
                <div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $a->contact?->name ?? '-' }}</div>
                    <div class="text-[10px] text-gray-400 font-mono">{{ preg_replace('/@.*$/', '', $a->contact?->phone ?? '') }}</div>
                </div>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700">Aktif</span>
        </div>
        <div class="text-xs text-gray-500 mb-3">
            <div><i class="fas fa-user-tie mr-1 text-gray-400"></i> Agen: <span class="font-medium text-gray-700">{{ $a->teamMember?->name ?? '-' }}</span></div>
            <div><i class="fas fa-clock mr-1 text-gray-400"></i> {{ $a->assigned_at?->diffForHumans() }}</div>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('inbox.reassign', $a) }}" class="flex-1 flex gap-1">
                @csrf
                <select name="team_member_id" class="flex-1 rounded-lg border border-gray-300 px-2 py-1.5 text-xs">
                    @foreach($members as $m)<option value="{{ $m->id }}" {{ $a->team_member_id==$m->id ? 'selected':'' }}>{{ $m->name }}</option>@endforeach
                </select>
                <button class="text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-2 py-1.5 rounded-lg font-medium"><i class="fas fa-exchange-alt"></i></button>
            </form>
            <form method="POST" action="{{ route('inbox.close', $a) }}" onsubmit="return confirm('Tutup percakapan?')">@csrf<button class="text-xs bg-red-50 text-red-700 hover:bg-red-100 px-2.5 py-1.5 rounded-lg font-medium">Tutup</button></form>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-inbox text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">Tidak ada percakapan aktif</p>
        <p class="text-sm text-gray-400">Tugaskan percakapan ke anggota tim</p>
    </div>
    @endforelse
</div>

{{-- Assign Modal --}}
<div id="assignModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Tugaskan Percakapan</h2>
        <form method="POST" action="{{ route('inbox.assign') }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs font-medium text-gray-500">Contact ID</label>
                <input type="number" name="contact_id" required placeholder="ID kontak" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Anggota Tim</label>
                <select name="team_member_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Pilih anggota...</option>
                    @foreach($members as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('assignModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Tugaskan</button>
            </div>
        </form>
    </div>
</div>
@endsection
