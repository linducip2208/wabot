@extends('layouts.app')

@section('title', __('publishing.campaigns_title') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-bullhorn text-brand-500 mr-2"></i>{{ __('publishing.post_campaigns') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('publishing.campaigns_subtitle', ['count' => $campaigns->count()]) }}</p>
    </div>
    <button onclick="document.getElementById('addCampaignModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> {{ __('publishing.add_campaign') }}
    </button>
</div>

<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($campaigns as $campaign)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition" style="border-left: 4px solid {{ $campaign->color }}">
        <div class="flex items-start justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-800">{{ $campaign->name }}</h3>
            <div class="flex items-center gap-1">
                <button onclick="editCampaign({{ $campaign->id }}, '{{ addslashes($campaign->name) }}', '{{ addslashes($campaign->description ?? '') }}', '{{ $campaign->color }}')" class="p-1 text-gray-400 hover:text-brand-600 transition"><i class="fas fa-edit text-xs"></i></button>
                <form action="{{ route('publishing.campaigns.destroy', $campaign) }}" method="POST" onsubmit="return confirm('{{ __('publishing.delete_confirm') }}')">
                    @csrf @method('DELETE')
                    <button class="p-1 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-xs"></i></button>
                </form>
            </div>
        </div>
        @if($campaign->description)
        <p class="text-sm text-gray-600 mb-2">{{ $campaign->description }}</p>
        @endif
        <div class="text-xs text-gray-500">
            <span class="font-medium">{{ $campaign->posts_count }}</span> {{ __('publishing.posts_count') }}
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-bullhorn text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1">{{ __('publishing.no_campaigns') }}</h3>
        <p class="text-sm text-gray-400">{{ __('publishing.no_campaigns_desc') }}</p>
    </div>
    @endforelse
</div>

{{-- Add Campaign Modal --}}
<div id="addCampaignModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.add_campaign') }}</h3>
            <button onclick="document.getElementById('addCampaignModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('publishing.campaigns.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="Summer Launch 2025">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.description') }}</label>
                <textarea name="description" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none" placeholder="{{ __('publishing.campaign_desc_placeholder') }}"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.color') }}</label>
                <input type="color" name="color" value="#3b82f6" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.save_campaign') }}
            </button>
        </form>
    </div>
</div>

{{-- Edit Campaign Modal --}}
<div id="editCampaignModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.edit_campaign') }}</h3>
            <button onclick="document.getElementById('editCampaignModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editCampaignForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" id="editCampaignName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.description') }}</label>
                <textarea id="editCampaignDesc" name="description" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.color') }}</label>
                <input type="color" id="editCampaignColor" name="color" class="w-10 h-10 rounded border border-gray-300 cursor-pointer">
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.update_campaign') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editCampaign(id, name, desc, color) {
    document.getElementById('editCampaignForm').action = '/publishing/campaigns/' + id;
    document.getElementById('editCampaignName').value = name;
    document.getElementById('editCampaignDesc').value = desc;
    document.getElementById('editCampaignColor').value = color;
    document.getElementById('editCampaignModal').classList.remove('hidden');
}
</script>
@endpush
@stop
