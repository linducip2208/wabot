@extends('layouts.app')
@section('title', __('common.contact') . ' — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('common.contact') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $contacts->total() }} {{ __('common.contact') }} {{ __('contacts.stored') }}</p>
    </div>
    <div class="flex gap-2">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-upload text-xs"></i> {{ __('contacts.import_csv') }}
        </button>
        <button onclick="toggleAddModal()"
            class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('common.create') }}
        </button>
    </div>
</div>

<form method="POST" action="{{ route('groups.assign') }}" id="assignForm">
    @csrf
    {{-- Bulk assign bar --}}
    <div id="bulkBar" class="hidden bg-brand-50 border border-brand-200 rounded-xl px-4 py-3 mb-3 flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-brand-800"><span id="bulkCount">0</span> {{ __('common.contact') }} {{ __('contacts.selected') }}</span>
        <select name="group_id" required class="rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <option value="">{{ __('contacts.select_group') }}</option>
            @foreach($groups as $g)
                <option value="{{ $g->id }}">{{ $g->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">
            <i class="fas fa-layer-group text-xs mr-1"></i> {{ __('contacts.assign_to_group') }}
        </button>
        <a href="{{ route('groups.index') }}" class="text-xs text-brand-600 hover:underline ml-auto">{{ __('contacts.manage_groups') }}</a>
    </div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-4 py-3 w-8"><input type="checkbox" id="checkAll" onchange="toggleAllContacts(this)" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"></th>
                <th class="px-5 py-3">{{ __('common.contact') }}</th>
                <th class="px-5 py-3">{{ __('contacts.number') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('contacts.group') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">Tags</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('contacts.last_chat') }}</th>
                <th class="px-5 py-3 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($contacts as $c)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-4 py-3"><input type="checkbox" name="contact_ids[]" value="{{ $c->id }}" onchange="updateBulkBar()" class="contact-check rounded border-gray-300 text-brand-600 focus:ring-brand-500"></td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold" style="background: {{ collect(['#2563eb','#7c3aed','#db2777','#ea580c','#059669','#0891b2'])->get(crc32($c->phone) % 6) }}">
                            {{ strtoupper(substr($c->name, 0, 2)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $c->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ preg_replace('/@.*$/', '', $c->phone) }}</td>
                <td class="px-5 py-3 hidden md:table-cell">
                    @forelse($c->groups as $grp)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-700 mr-1">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $grp->color ?? '#3b82f6' }}"></span>{{ $grp->name }}
                        </span>
                    @empty
                        <span class="text-gray-400 text-xs">-</span>
                    @endforelse
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    @if($c->tags)
                        @foreach($c->tags as $tag)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium bg-gray-100 text-gray-600 mr-1">{{ $tag }}</span>
                        @endforeach
                    @else
                        <span class="text-gray-400 text-xs">-</span>
                    @endif
                </td>
                <td class="px-5 py-3 hidden lg:table-cell text-xs text-gray-400">
                    {{ $c->messages->last()?->created_at?->diffForHumans() ?? '-' }}
                </td>
                <td class="px-5 py-3 text-right">
                    <button type="button" onclick='editContact({{ $c->id }}, "{{ addslashes($c->name) }}", "{{ $c->phone }}", {{ json_encode($c->tags ? implode(',', $c->tags) : '') }})'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <button type="button" onclick="if(confirm('{{ __('common.delete') }}?')) document.getElementById('del-{{ $c->id }}').submit()"
                        class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-address-book text-gray-400 text-lg"></i>
                </div>
                <p class="text-gray-500 font-medium">{{ __('contacts.empty_title') }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ __('contacts.empty_desc') }}</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</form>

@foreach($contacts as $c)
<form method="POST" action="{{ route('contacts.destroy', $c) }}" id="del-{{ $c->id }}" class="hidden">
    @csrf @method('DELETE')
</form>
@endforeach

<div class="mt-4">{{ $contacts->links() }}</div>

{{-- Add/{{ __('common.edit') }} Modal --}}
<div id="contactModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="contactModalTitle">{{ __('common.create') }} {{ __('common.contact') }}</h2>
        <form method="POST" action="{{ route('contacts.store') }}" class="space-y-3" id="contactForm">
            @csrf
            <div id="contactMethodField"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" placeholder="{{ __('common.name') }} {{ __('common.contact') }}" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('contacts.phone') }}</label>
                <input type="text" name="phone" placeholder="6281234567890" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('contacts.tags') }} <span class="text-gray-400">{{ __('contacts.tags_hint') }}</span></label>
                <input type="text" name="tags" placeholder="VIP, Leads" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="toggleAddModal()" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

{{-- Import Modal --}}
<div id="importModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">{{ __('contacts.import_csv_title') }}</h2>
        <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
                <p class="font-medium mb-2">{{ __('contacts.file_format') }}</p>
                <code class="text-xs bg-white px-2 py-1 rounded border border-gray-200 block">{{ __('common.name') }}, nomor, tag1,tag2</code>
            </div>
            <input type="file" name="file" accept=".csv,.txt" required class="w-full text-sm">
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700"><i class="fas fa-upload mr-1"></i> {{ __('common.import') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAllContacts(el) {
    document.querySelectorAll('.contact-check').forEach(c => c.checked = el.checked);
    updateBulkBar();
}
function updateBulkBar() {
    const checked = document.querySelectorAll('.contact-check:checked').length;
    document.getElementById('bulkBar').classList.toggle('hidden', checked === 0);
    document.getElementById('bulkCount').textContent = checked;
}
function toggleAddModal() {
    const m = document.getElementById('contactModal');
    m.classList.toggle('hidden');
    if (!m.classList.contains('hidden')) {
        document.getElementById('contactModalTitle').textContent = '{{ __('common.create') }} {{ __('common.contact') }}';
        const f = document.getElementById('contactForm');
        f.action = '{{ route('contacts.store') }}';
        f.querySelector('input[name="name"]').value = '';
        f.querySelector('input[name="phone"]').value = '';
        f.querySelector('input[name="tags"]').value = '';
        document.getElementById('contactMethodField').innerHTML = '';
    }
}
function editContact(id, name, phone, tags) {
    const m = document.getElementById('contactModal');
    m.classList.remove('hidden');
    document.getElementById('contactModalTitle').textContent = '{{ __('common.edit') }} {{ __('common.contact') }}';
    const f = document.getElementById('contactForm');
    f.action = '/contacts/' + id;
    f.querySelector('input[name="name"]').value = name;
    f.querySelector('input[name="phone"]').value = phone;
    f.querySelector('input[name="tags"]').value = tags || '';
    document.getElementById('contactMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
