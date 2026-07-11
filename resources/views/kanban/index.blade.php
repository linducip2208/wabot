@extends('layouts.app')
@section('title', 'Kanban Board — WABot')

@section('content')
<div class="max-w-full mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900">Kanban Board</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('kanban.drag_drop_hint') }}</p>
        </div>
        <a href="{{ route('contact-tags.index') }}"
            class="bg-white border border-gray-300 text-gray-700 px-3.5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
            <i class="fas fa-cog mr-1"></i> {{ __('kanban.manage_tags') }}
        </a>
    </div>

    @php
        $columns = [];
        $columns['Belum Ditandai'] = $contacts['Belum Ditandai'] ?? collect();
        foreach ($tags as $tag) {
            $columns[$tag->name] = $contacts[$tag->name] ?? collect();
        }
    @endphp

    <div class="flex gap-3 overflow-x-auto pb-6" style="min-height: 60vh;">
        @foreach($columns as $columnName => $columnContacts)
            @php $displayName = $columnName === 'Belum Ditandai' ? __('kanban.untagged') : $columnName; @endphp
            <div class="flex-shrink-0 w-72 bg-gray-50 rounded-xl p-3"
                data-column="{{ $columnName }}"
                data-tag-id="{{ $columnName === 'Belum Ditandai' ? '' : ($tags->firstWhere('name', $columnName)?->id ?? '') }}"
                ondragover="event.preventDefault()"
                ondrop="handleDrop(event, this)">
                <div class="flex items-center justify-between mb-3 px-1">
                    <h3 class="text-sm font-semibold text-gray-700">{{ $displayName }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white text-gray-500 border border-gray-200">
                        {{ $columnContacts->count() }}
                    </span>
                </div>
                <div class="space-y-2 min-h-[80px]">
                    @foreach($columnContacts as $contact)
                        <div class="bg-white rounded-xl border border-gray-200 p-3 cursor-grab hover:shadow-sm hover:border-brand-200 transition"
                            draggable="true"
                            data-contact-id="{{ $contact->id }}"
                            ondragstart="event.dataTransfer.setData('text/plain', '{{ $contact->id }}'); this.style.opacity='0.5'"
                            ondragend="this.style.opacity='1'">
                            <p class="text-sm font-medium text-gray-900">{{ $contact->name }}</p>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $contact->display_phone ?? Str::limit($contact->phone, 20) }}</p>
                            @if($contact->messages->first())
                                <p class="text-xs text-gray-500 mt-1.5 line-clamp-2">
                                    {{ Str::limit($contact->messages->first()->message, 80) }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                    <div class="text-center text-xs text-gray-400 py-4 {{ $columnContacts->isEmpty() ? '' : 'hidden' }}">
                        {{ __('kanban.drop_contact_here') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleDrop(event, column) {
    event.preventDefault();
    const contactId = event.dataTransfer.getData('text/plain');
    const tagId = column.dataset.tagId;

    fetch('{{ route('kanban.move') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            contact_id: contactId,
            tag_id: tagId || null,
        }),
    }).then(r => r.json()).then(data => {
        if (data.ok) location.reload();
    });
}
</script>
@endpush
