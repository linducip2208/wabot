@extends('layouts.app')
@section('title', __('media.title') . ' — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('media.title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('media.subtitle', ['count' => $templates->count()]) }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('media.create_button') }}
    </button>
</div>

@php $typeIcon = ['image'=>'fa-image','video'=>'fa-video','audio'=>'fa-music','document'=>'fa-file-alt','sticker'=>'fa-sticky-note','location'=>'fa-map-marker-alt']; @endphp

@forelse($grouped as $type => $items)
<div class="mb-5">
    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2"><i class="fas {{ $typeIcon[$type] ?? 'fa-file' }} mr-1"></i> {{ ucfirst($type) }} ({{ $items->count() }})</h2>
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($items as $t)
        <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-pink-50 flex items-center justify-center"><i class="fas {{ $typeIcon[$type] ?? 'fa-file' }} text-pink-500"></i></div>
                    <div class="font-semibold text-gray-900 text-sm">{{ $t->name }}</div>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick='editMedia(@json($t))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                    <form method="POST" action="{{ route('media-templates.destroy', $t) }}" onsubmit="return confirm('{{ __('common.delete') }}?')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
                </div>
            </div>
            @if($type === 'image' && $t->media_url)<img src="{{ $t->media_url }}" class="w-full h-28 object-cover rounded-lg mb-2 border border-gray-100">@endif
            @if($type === 'location')<div class="text-xs text-gray-500 font-mono mb-1">{{ $t->latitude }}, {{ $t->longitude }}</div>@endif
            @if($t->caption)<p class="text-xs text-gray-500 line-clamp-2">{{ $t->caption }}</p>@endif
        </div>
        @endforeach
    </div>
</div>
@empty
<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-photo-video text-2xl text-gray-400"></i></div>
    <p class="text-gray-500 font-medium mb-1">{{ __('media.empty_title') }}</p>
    <p class="text-sm text-gray-400 mb-4">{{ __('media.empty_desc') }}</p>
    <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> {{ __('media.create_button') }}</button>
</div>
@endforelse

{{-- Modal --}}
<div id="mediaModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl" onclick="event.stopPropagation()" x-data="{ type: 'image' }">
        <h2 class="text-lg font-bold mb-4" id="mediaModalTitle">{{ __('media.create_title') }}</h2>
        <form method="POST" action="{{ route('media-templates.store') }}" enctype="multipart/form-data" class="space-y-3" id="mediaForm">
            @csrf
            <div id="mediaMethod"></div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                    <input type="text" name="name" required class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('media.type') }}</label>
                    <select name="type" x-model="type" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                        <option value="image">{{ __('media.image') }}</option><option value="video">{{ __('media.video') }}</option><option value="audio">{{ __('media.audio') }}</option><option value="document">{{ __('media.document') }}</option><option value="sticker">{{ __('media.sticker') }}</option><option value="location">{{ __('media.location') }}</option>
                    </select>
                </div>
            </div>
            <div x-show="type !== 'location'">
                <label class="text-xs font-medium text-gray-500">Upload File</label>
                <input type="file" name="file" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:border-0 file:bg-brand-50 file:text-brand-700 file:rounded-lg file:text-xs">
                <p class="text-[11px] text-gray-400 mt-1">atau</p>
            </div>
            <div x-show="type !== 'location'">
                <label class="text-xs font-medium text-gray-500">Media URL</label>
                <input type="url" name="media_url" placeholder="https://..." class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div x-show="type === 'location'" class="grid grid-cols-2 gap-3">
                <div><label class="text-xs font-medium text-gray-500">Latitude</label><input type="number" step="any" name="latitude" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
                <div><label class="text-xs font-medium text-gray-500">Longitude</label><input type="number" step="any" name="longitude" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></div>
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('media.caption_optional') }}</label>
                <textarea name="caption" rows="2" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm"></textarea>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('mediaModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('mediaModal'); m.classList.remove('hidden');
    document.getElementById('mediaModalTitle').textContent = '{{ __('media.create_title') }}';
    const f = document.getElementById('mediaForm'); f.action = '{{ route('media-templates.store') }}'; f.reset();
    document.getElementById('mediaMethod').innerHTML = '';
}
function editMedia(t) {
    const m = document.getElementById('mediaModal'); m.classList.remove('hidden');
    document.getElementById('mediaModalTitle').textContent = '{{ __('media.edit_title') }}';
    const f = document.getElementById('mediaForm'); f.action = '/media-templates/' + t.id;
    f.querySelector('[name="name"]').value = t.name;
    f.querySelector('[name="type"]').value = t.type;
    f.querySelector('[name="type"]').dispatchEvent(new Event('change'));
    f.querySelector('[name="media_url"]').value = t.media_url || '';
    f.querySelector('[name="caption"]').value = t.caption || '';
    f.querySelector('[name="latitude"]').value = t.latitude || '';
    f.querySelector('[name="longitude"]').value = t.longitude || '';
    document.getElementById('mediaMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
