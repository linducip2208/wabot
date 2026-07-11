@extends('layouts.app')
@section('title', __('tags.title') . ' — WABot')
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('tags.title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('tags.subtitle', ['count' => $tags->count()]) }}</p>
    </div>
    <button onclick="openModal()" class="bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus text-xs"></i> {{ __('tags.create_button') }}
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
    @forelse($tags as $tag)
    <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-white" style="background: {{ $tag->color }}"><i class="fas fa-tag"></i></span>
                <div>
                    <div class="font-semibold text-gray-900">{{ $tag->name }}</div>
                    <div class="text-xs text-gray-400">{{ $tag->contacts_count }} {{ __('common.contact') }}</div>
                </div>
            </div>
            <div class="flex items-center gap-1">
                <button onclick='editTag(@json($tag))' class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600"><i class="fas fa-edit text-xs"></i></button>
                <form method="POST" action="{{ route('contact-tags.destroy', $tag) }}" onsubmit="return confirm('{{ __('tags.delete_confirm') }}')">@csrf @method('DELETE')<button class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i class="fas fa-trash text-xs"></i></button></form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-xl border border-gray-200 p-16 text-center">
        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-tags text-2xl text-gray-400"></i></div>
        <p class="text-gray-500 font-medium mb-1">{{ __('tags.empty_title') }}</p>
        <p class="text-sm text-gray-400 mb-4">{{ __('tags.empty_desc') }}</p>
        <button onclick="openModal()" class="inline-flex items-center gap-2 bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-700 transition"><i class="fas fa-plus text-xs"></i> {{ __('tags.create_button') }}</button>
    </div>
    @endforelse
</div>

{{-- Modal --}}
<div id="tagModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4" id="tagModalTitle">{{ __('tags.create_title') }}</h2>
        <form method="POST" action="{{ route('contact-tags.store') }}" class="space-y-3" id="tagForm">
            @csrf
            <div id="tagMethod"></div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" name="name" required placeholder="VIP" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
            </div>
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.color') }}</label>
                <input type="color" name="color" value="#3b82f6" required class="w-full h-10 rounded-xl border border-gray-300 cursor-pointer">
            </div>
            <div class="flex gap-2 pt-1">
                <button type="button" onclick="document.getElementById('tagModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2.5 text-sm font-medium">{{ __('common.cancel') }}</button>
                <button type="submit" class="flex-1 bg-brand-600 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    const m = document.getElementById('tagModal'); m.classList.remove('hidden');
    document.getElementById('tagModalTitle').textContent = '{{ __('tags.create_title') }}';
    const f = document.getElementById('tagForm'); f.action = '{{ route('contact-tags.store') }}'; f.reset();
    f.querySelector('[name="color"]').value = '#3b82f6';
    document.getElementById('tagMethod').innerHTML = '';
}
function editTag(t) {
    const m = document.getElementById('tagModal'); m.classList.remove('hidden');
    document.getElementById('tagModalTitle').textContent = '{{ __('tags.edit_title') }}';
    const f = document.getElementById('tagForm'); f.action = '/contact-tags/' + t.id;
    f.querySelector('[name="name"]').value = t.name;
    f.querySelector('[name="color"]').value = t.color;
    document.getElementById('tagMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
}
</script>
@endsection
