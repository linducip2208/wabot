@extends('layouts.app')
@section('title', 'Flow Builder — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">Flow Builder</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $flows->count() }} {{ __('flows.subtitle') }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('flows.new_flow') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-5 py-3">{{ __('common.name') }} Flow</th>
                <th class="px-5 py-3 hidden md:table-cell">{{ __('flows.trigger') }}</th>
                <th class="px-5 py-3">{{ __('flows.nodes') }}</th>
                <th class="px-5 py-3">{{ __('common.status') }}</th>
                <th class="px-5 py-3 w-40 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($flows as $flow)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                            <i class="fas fa-project-diagram text-indigo-500"></i>
                        </div>
                        <div>
                            <div class="font-semibold text-gray-900">{{ $flow->name }}</div>
                            <div class="text-xs text-gray-400 line-clamp-1">{{ $flow->description ?: __('flows.no_description') }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 hidden md:table-cell">
                    <span class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">{{ $flow->trigger_keyword }}</span>
                    <span class="text-[10px] text-gray-400 ml-1">{{ ['exact'=>__('flows.match_exact'),'contains'=>__('flows.match_contains'),'starts_with'=>__('flows.match_starts_with')][$flow->trigger_match_type] ?? $flow->trigger_match_type }}</span>
                </td>
                <td class="px-5 py-3"><span class="inline-flex items-center gap-1 text-gray-600"><i class="fas fa-circle-nodes text-xs text-gray-400"></i> {{ $flow->nodes_count }}</span></td>
                <td class="px-5 py-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $flow->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $flow->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ $flow->is_active ? __('common.active') : __('common.inactive') }}
                    </span>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('flows.nodes', $flow) }}" class="inline-flex items-center gap-1 text-[11px] bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg font-medium"><i class="fas fa-sitemap"></i> {{ __('flows.builder') }}</a>
                    <button onclick='editFlow(@json($flow))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('flows.destroy', $flow) }}" class="inline" onsubmit="return confirm('{{ __('flows.confirm_delete') }}')">
                        @csrf @method('DELETE')
                        <button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3"><i class="fas fa-project-diagram text-gray-400 text-lg"></i></div>
                <p class="text-gray-500 font-medium">{{ __('flows.empty_title') }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ __('flows.empty_subtitle') }}</p>
            </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Modal --}}
<div id="flowModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="flowModalTitle">{{ __('flows.new_flow') }}</h2>
        <form method="POST" action="{{ route('flows.store') }}" class="space-y-3" id="flowForm">
            @csrf
            <div id="flowMethod"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }} Flow</label>
                <input type="text" name="name" required placeholder="{{ __('flows.name_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.description') }}</label>
                <textarea name="description" rows="2" placeholder="{{ __('flows.description_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('flows.trigger_keyword') }}</label>
                    <input type="text" name="trigger_keyword" required placeholder="mulai" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('flows.match_type') }}</label>
                    <select name="trigger_match_type" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="contains">{{ __('flows.match_contains') }}</option>
                        <option value="exact">{{ __('flows.match_exact') }}</option>
                        <option value="starts_with">{{ __('flows.match_starts_with') }}</option>
                    </select>
                </div>
            </div>
            <div id="flowActiveGroup" class="hidden">
                <label class="text-xs font-medium text-gray-500">{{ __('common.status') }}</label>
                <select name="is_active" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                    <option value="1">{{ __('common.active') }}</option>
                    <option value="0">{{ __('common.inactive') }}</option>
                </select>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('flowModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('flowModal'); m.classList.remove('hidden');
    document.getElementById('flowModalTitle').textContent = '{{ __('flows.new_flow') }}';
    const f = document.getElementById('flowForm');
    f.action = '{{ route('flows.store') }}';
    f.reset();
    document.getElementById('flowMethod').innerHTML = '';
    document.getElementById('flowActiveGroup').classList.add('hidden');
}
function editFlow(flow) {
    const m = document.getElementById('flowModal'); m.classList.remove('hidden');
    document.getElementById('flowModalTitle').textContent = '{{ __('flows.edit_flow') }}';
    const f = document.getElementById('flowForm');
    f.action = '/flows/' + flow.id;
    f.querySelector('[name="name"]').value = flow.name;
    f.querySelector('[name="description"]').value = flow.description || '';
    f.querySelector('[name="trigger_keyword"]').value = flow.trigger_keyword;
    f.querySelector('[name="trigger_match_type"]').value = flow.trigger_match_type;
    f.querySelector('[name="is_active"]').value = flow.is_active ? '1' : '0';
    document.getElementById('flowActiveGroup').classList.remove('hidden');
    document.getElementById('flowMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
