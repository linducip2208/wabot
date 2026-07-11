@extends('layouts.app')

@section('title', __('publishing.labels_title') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-tags text-brand-500 mr-2"></i>{{ __('publishing.labels') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('publishing.labels_subtitle', ['count' => $labels->count()]) }}</p>
    </div>
    <button onclick="document.getElementById('addLabelModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> {{ __('publishing.add_label') }}
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
    @forelse($labels as $label)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-4 hover:shadow-md transition">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full" style="background:{{ $label->color }}"></span>
                <span class="text-sm font-semibold text-gray-800">{{ $label->name }}</span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="editLabel({{ $label->id }}, '{{ addslashes($label->name) }}', '{{ $label->color }}')" class="p-1 text-gray-400 hover:text-brand-600 transition"><i class="fas fa-edit text-xs"></i></button>
                <form action="{{ route('publishing.labels.destroy', $label) }}" method="POST" onsubmit="return confirm('{{ __('publishing.delete_confirm') }}')">
                    @csrf @method('DELETE')
                    <button class="p-1 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        <div class="text-xs text-gray-500">{{ $label->posts_count }} {{ __('publishing.posts_count') }}</div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-tags text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1">{{ __('publishing.no_labels') }}</h3>
        <p class="text-sm text-gray-400">{{ __('publishing.no_labels_desc') }}</p>
    </div>
    @endforelse
</div>

{{-- Add Label Modal --}}
<div id="addLabelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.add_label') }}</h3>
            <button onclick="document.getElementById('addLabelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('publishing.labels.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Promo">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.color') }}</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="color" value="#3b82f6" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
                    <span class="text-xs text-gray-500">{{ __('publishing.color_hint') }}</span>
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.save_label') }}
            </button>
        </form>
    </div>
</div>

{{-- Edit Label Modal --}}
<div id="editLabelModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.edit_label') }}</h3>
            <button onclick="document.getElementById('editLabelModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editLabelForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" id="editLabelName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.color') }}</label>
                <input type="color" id="editLabelColor" name="color" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.update_label') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editLabel(id, name, color) {
    document.getElementById('editLabelForm').action = '/publishing/labels/' + id;
    document.getElementById('editLabelName').value = name;
    document.getElementById('editLabelColor').value = color;
    document.getElementById('editLabelModal').classList.remove('hidden');
}
</script>
@endpush
@stop
