@extends('layouts.app')
@section('title', 'Team Members — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Team Members</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('team.subtitle', ['count' => $members->count()]) }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('team.create_member') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.name') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">Email</th>
                <th class="px-5 py-3">{{ __('common.role') }}</th>
                <th class="px-5 py-3">{{ __('team.workload') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($members as $m)
            <tr class="hover:bg-gray-50/50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-bold">{{ strtoupper(substr($m->name, 0, 2)) }}</div>
                        <span class="font-medium text-gray-900">{{ $m->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell text-gray-600">{{ $m->email }}</td>
                <td class="px-5 py-3"><span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 capitalize">{{ $m->role }}</span></td>
                <td class="px-5 py-3 text-gray-600">{{ $m->active_conversations_count }}/{{ $m->max_concurrent }}</td>
                <td class="px-5 py-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $m->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}"><span class="w-1.5 h-1.5 rounded-full {{ $m->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>{{ $m->is_active ? __('common.active') : __('common.inactive') }}</span></td>
                <td class="px-5 py-3 text-right">
                    <button onclick='editMember(@json($m))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('team-members.destroy', $m) }}" class="inline" onsubmit="return confirm('{{ __('team.delete_confirm') }}')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-users text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium">{{ __('team.no_members') }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ __('team.no_members_hint') }}</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="memberModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="memberModalTitle">{{ __('team.create_member') }}</h2>
        <form method="POST" action="{{ route('team-members.store') }}" class="space-y-3" id="memberForm">
            @csrf
            <div id="memberMethod"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">Email</label>
                <input type="email" name="email" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.password') }} <span class="text-gray-400" id="pwdHint">({{ __('team.min_6_chars') }})</span></label>
                <input type="password" name="password" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.role') }}</label>
                    <select name="role" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="agent">Agent</option><option value="supervisor">Supervisor</option><option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">Max Concurrent</label>
                    <input type="number" name="max_concurrent" min="1" max="100" value="5" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('memberModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('memberModal'); m.classList.remove('hidden');
    document.getElementById('memberModalTitle').textContent = '{{ __('team.create_member') }}';
    const f = document.getElementById('memberForm'); f.action = '{{ route('team-members.store') }}'; f.reset();
    f.querySelector('[name="password"]').required = true;
    document.getElementById('pwdHint').textContent = '({{ __('team.min_6_chars') }})';
    document.getElementById('memberMethod').innerHTML = '';
}
function editMember(m) {
    const modal = document.getElementById('memberModal'); modal.classList.remove('hidden');
    document.getElementById('memberModalTitle').textContent = '{{ __('team.edit_member') }}';
    const f = document.getElementById('memberForm'); f.action = '/team-members/' + m.id;
    f.querySelector('[name="name"]').value = m.name;
    f.querySelector('[name="email"]').value = m.email;
    f.querySelector('[name="password"]').value = '';
    f.querySelector('[name="password"]').required = false;
    f.querySelector('[name="role"]').value = m.role;
    f.querySelector('[name="max_concurrent"]').value = m.max_concurrent;
    document.getElementById('pwdHint').textContent = '({{ __('team.leave_empty') }})';
    document.getElementById('memberMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
