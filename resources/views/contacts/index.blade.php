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

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.contact') }}</th>
                <th class="px-5 py-3">{{ __('contacts.number') }}</th>
                <th class="px-5 py-3 hidden md:table-cell">Tags</th>
                <th class="px-5 py-3 hidden lg:table-cell">{{ __('contacts.last_chat') }}</th>
                <th class="px-5 py-3 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($contacts as $c)
            <tr class="hover:bg-gray-50/50 transition">
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
                    <button onclick='editContact({{ $c->id }}, "{{ addslashes($c->name) }}", "{{ $c->phone }}", {{ json_encode($c->tags ? implode(',', $c->tags) : '') }})'
                        class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('contacts.destroy', $c) }}" class="inline" onsubmit="return confirm('{{ __('common.delete') }}?')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-16 text-center">
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
