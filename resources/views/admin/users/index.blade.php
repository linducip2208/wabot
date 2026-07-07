@extends('layouts.app')
@section('title', 'User — Admin')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Manajemen User</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $users->count() }} user terdaftar</p>
    </div>
    <button onclick="toggleModal()"
        class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> Tambah User
    </button>
</div>

{{-- Stat Bar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center"><i class="fas fa-users text-sky-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Total User</div><div class="text-xl font-extrabold text-gray-900">{{ $users->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i class="fas fa-crown text-emerald-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Growth+</div><div class="text-xl font-extrabold text-gray-900">{{ $users->filter(fn($u) => $u->plan && $u->plan->price > 0)->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i class="fas fa-gift text-amber-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Free</div><div class="text-xl font-extrabold text-gray-900">{{ $users->filter(fn($u) => !$u->plan || $u->plan->price == 0)->count() }}</div></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-calendar text-violet-500"></i></div>
        <div><div class="text-[11px] font-medium text-gray-500 uppercase tracking-wide">Bulan Ini</div><div class="text-xl font-extrabold text-gray-900">{{ $users->where('created_at', '>=', now()->startOfMonth())->count() }}</div></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3 hidden md:table-cell">Email</th>
                <th class="px-5 py-3">Paket</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3 hidden lg:table-cell">Daftar</th>
                <th class="px-5 py-3 w-24 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($users as $u)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: {{ collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($u->name) % 6) }}">
                            {{ strtoupper(substr($u->name, 0, 2)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $u->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-600 hidden md:table-cell">{{ $u->email }}</td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $u->plan && $u->plan->price > 0 ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $u->plan?->name ?? 'Free' }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ is_object($u->role) && $u->role->name === 'admin' ? 'bg-violet-50 text-violet-700' : 'bg-sky-50 text-sky-700' }}">
                        {{ is_object($u->role) ? $u->role->name : ucfirst($u->getAttribute('role') ?? 'user') }}
                    </span>
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">{{ $u->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3 text-right">
                    <form method="POST" action="{{ route('admin.users.impersonate', $u) }}" class="inline">
                        @csrf
                        <button class="p-1.5 rounded-lg hover:bg-violet-50 text-gray-400 hover:text-violet-600" title="Login sebagai"><i class="fas fa-sign-in-alt text-xs"></i></button>
                    </form>
                    <button onclick='editUser({{ $u->id }}, "{{ addslashes($u->name) }}", "{{ $u->email }}", {{ $u->role_id ?? 'null' }}, {{ $u->plan_id ?? 'null' }})'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus user ini?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="userModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="modalTitle">Tambah User</h2>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3" id="userForm">
            @csrf
            <div id="methodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">Nama</label>
                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Email</label>
                <input type="email" name="email" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Password</label>
                <input type="password" name="password" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Role</label>
                <select name="role_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Paket</label>
                <select name="plan_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="">Tanpa Paket</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} {{ $p->price > 0 ? '(Rp '.number_format($p->price).')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleModal() {
    const m = document.getElementById('userModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('modalTitle').textContent = 'Tambah User';
        const f = document.getElementById('userForm');
        f.action = '{{ route('admin.users.store') }}';
        f.reset();
        document.getElementById('methodField').innerHTML = '';
    }
}
function editUser(id, name, email, roleId, planId) {
    const m = document.getElementById('userModal');
    m.classList.remove('hidden');
    document.getElementById('modalTitle').textContent = 'Edit User';
    const f = document.getElementById('userForm');
    f.action = '/admin/users/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="email"]').value = email;
    f.querySelector('input[name="password"]').value = '';
    f.querySelector('input[name="password"]').required = false;
    f.querySelector('select[name="role_id"]').value = roleId || '';
    f.querySelector('select[name="plan_id"]').value = planId || '';
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
