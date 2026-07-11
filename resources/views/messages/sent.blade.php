@extends('layouts.app')
@section('title', __('common.message') . ' ' . __('common.sent') . ' — WABot')
@section('content')

<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('common.message') }} {{ __('common.sent') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $messages->total() }} {{ __('common.message') }} {{ __('messages.outgoing_count') }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('messages.received') }}" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-inbox mr-1"></i> {{ __('messages.inbox') }}
        </a>
        <a href="{{ route('messages.sent') }}" class="bg-blue-600 text-white px-3 py-2 rounded-xl text-sm font-medium">
            <i class="fas fa-paper-plane mr-1"></i> {{ __('common.sent') }}
        </a>
        <a href="{{ route('messages.queue') }}" class="bg-white border border-gray-200 text-gray-700 px-3 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
            <i class="fas fa-clock mr-1"></i> {{ __('messages.queue') }}
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="flex gap-2 mb-4 flex-wrap">
    <select onchange="window.location=this.value" class="rounded-xl border border-gray-300 px-3 py-2 text-xs">
        <option value="?">{{ __('common.all') }} {{ __('common.session') }}</option>
        @foreach($sessions as $s)
            <option value="?session_id={{ $s->id }}" {{ request('session_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>
    <form id="bulkForm" method="POST" action="{{ route('messages.bulk-delete') }}" class="hidden">
        @csrf
        <input type="hidden" name="ids" id="bulkIds">
    </form>
    <button onclick="bulkDelete()" class="text-xs bg-red-50 text-red-600 px-3 py-2 rounded-xl hover:bg-red-100 transition font-medium">
        <i class="fas fa-trash mr-1"></i> {{ __('common.delete_selected') }}
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                <th class="px-3 py-2.5 w-8"><input type="checkbox" onchange="toggleAll(this)" class="rounded"></th>
                <th class="px-3 py-2.5">{{ __('common.receiver') }}</th>
                <th class="px-3 py-2.5 hidden md:table-cell">{{ __('common.sender') }} (WA)</th>
                <th class="px-3 py-2.5 hidden md:table-cell">{{ __('common.message') }}</th>
                <th class="px-3 py-2.5">{{ __('common.status') }}</th>
                <th class="px-3 py-2.5">{{ __('common.session') }}</th>
                <th class="px-3 py-2.5">{{ __('common.status') }}</th>
                <th class="px-3 py-2.5 hidden lg:table-cell">{{ __('common.time') }}</th>
                <th class="px-3 py-2.5 w-20 text-right">{{ __('common.action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($messages as $m)
            <tr class="hover:bg-gray-50/50 transition">
                <td class="px-3 py-2.5"><input type="checkbox" value="{{ $m->id }}" class="msg-check rounded"></td>
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
                        {{ $m->status === 'sent' ? 'bg-emerald-50 text-emerald-700' : '' }}
                        {{ $m->status === 'failed' ? 'bg-red-50 text-red-700' : '' }}
                        {{ $m->status === 'pending' ? 'bg-amber-50 text-amber-700' : '' }}">
                        @php
                        $sentLabels = ['sent'=>'common.sent','failed'=>'common.failed','pending'=>'common.pending','sending'=>'common.sending'];
                        @endphp
                        {{ __($sentLabels[$m->status] ?? $m->status) }}
                    </span>
                </td>
                <td class="px-3 py-2.5 hidden lg:table-cell text-xs text-gray-400">{{ $m->created_at->format('d M H:i') }}</td>
                <td class="px-3 py-2.5 text-right">
                    @if($m->status === 'failed')
                    <form method="POST" action="{{ route('messages.resend', $m) }}" class="inline">
                        @csrf
                        <button class="p-1 rounded hover:bg-amber-50 text-gray-400 hover:text-amber-600 text-xs" title="{{ __('messages.resend') }}"><i class="fas fa-redo"></i></button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('messages.destroy', $m) }}" class="inline" onsubmit="return confirm('{{ __('common.delete') }}?')">
                        @csrf @method('DELETE')
                        <button class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600 text-xs"><i class="fas fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-4 py-16 text-center text-gray-500">{{ __('messages.empty_sent') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $messages->links() }}</div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.msg-check').forEach(cb => cb.checked = el.checked);
}
function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.msg-check:checked')).map(cb => cb.value);
    if (!ids.length) return alert('{{ __('messages.select_first') }}');
    if (!confirm('{{ __('common.delete') }} ' + ids.length + ' {{ __('common.message') }}?')) return;
    document.getElementById('bulkIds').value = JSON.stringify(ids);
    document.getElementById('bulkForm').submit();
}
</script>
@endsection
