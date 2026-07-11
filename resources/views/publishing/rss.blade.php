@extends('layouts.app')

@section('title', __('publishing.rss_title') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div>
        <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-rss text-brand-500 mr-2"></i>{{ __('publishing.rss_feeds') }}</h1>
        <p class="text-gray-500 text-sm mt-1">{{ __('publishing.rss_subtitle', ['count' => $schedules->count()]) }}</p>
    </div>
    <button onclick="document.getElementById('addRssModal').classList.remove('hidden')" class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition flex items-center gap-2">
        <i class="fas fa-plus"></i> {{ __('publishing.add_rss') }}
    </button>
</div>

<div class="space-y-4">
    @forelse($schedules as $schedule)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-sm font-semibold text-gray-800">{{ $schedule->name }}</h3>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $schedule->is_active ? __('publishing.active') : __('publishing.inactive') }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mb-2 truncate">{{ $schedule->feed_url }}</p>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span><i class="far fa-clock"></i> {{ __('publishing.every') }} {{ $schedule->interval_minutes }} {{ __('publishing.minutes') }}</span>
                    <span><i class="fas fa-history"></i> {{ $schedule->histories_count }} {{ __('publishing.posts_created') }}</span>
                    @if($schedule->last_checked_at)
                    <span>{{ __('publishing.last_checked') }}: {{ $schedule->last_checked_at->diffForHumans() }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-1 mt-2">
                    @foreach($schedule->platform_targets ?? [] as $p)
                        @php $icon = match($p) { 'facebook_page' => 'fab fa-facebook text-blue-600', 'instagram_professional' => 'fab fa-instagram text-pink-600', 'x_twitter' => 'fab fa-x-twitter', 'tiktok' => 'fab fa-tiktok', 'linkedin_page' => 'fab fa-linkedin text-blue-700', default => '' }; @endphp
                        <i class="{{ $icon }} text-xs" title="{{ $p }}"></i>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-1 ml-4 flex-shrink-0">
                <form action="{{ route('publishing.rss.toggle', $schedule) }}" method="POST">
                    @csrf
                    <button class="p-2 text-gray-400 hover:text-gray-700 transition" title="{{ $schedule->is_active ? __('publishing.deactivate') : __('publishing.activate') }}">
                        <i class="fas {{ $schedule->is_active ? 'fa-pause' : 'fa-play' }} text-sm"></i>
                    </button>
                </form>
                <button onclick="editRss({{ $schedule->id }}, '{{ addslashes($schedule->name) }}', '{{ addslashes($schedule->feed_url) }}', {{ json_encode($schedule->platform_targets) }}, {{ $schedule->interval_minutes }})" class="p-2 text-gray-400 hover:text-brand-600 transition">
                    <i class="fas fa-edit text-sm"></i>
                </button>
                <form action="{{ route('publishing.rss.destroy', $schedule) }}" method="POST" onsubmit="return confirm('{{ __('publishing.delete_confirm') }}')">
                    @csrf @method('DELETE')
                    <button class="p-2 text-gray-400 hover:text-red-500 transition"><i class="fas fa-trash text-sm"></i></button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
        <i class="fas fa-rss text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-500 mb-1">{{ __('publishing.no_rss') }}</h3>
        <p class="text-sm text-gray-400">{{ __('publishing.no_rss_desc') }}</p>
    </div>
    @endforelse
</div>

{{-- Add RSS Modal --}}
<div id="addRssModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.add_rss_feed') }}</h3>
            <button onclick="document.getElementById('addRssModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('publishing.rss.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="{{ __('publishing.rss_name_placeholder') }}">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.feed_url') }}</label>
                <input type="url" name="feed_url" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="https://example.com/rss">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.platforms') }}</label>
                <div class="space-y-1">
                    @foreach(App\Models\WaSocialAccount::platforms() as $key => $name)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="platform_targets[]" value="{{ $key }}" class="rounded text-brand-600 focus:ring-brand-500">
                        <span>{{ $name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.check_interval') }}</label>
                <select name="interval_minutes" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="15">15 {{ __('publishing.minutes') }}</option>
                    <option value="30">30 {{ __('publishing.minutes') }}</option>
                    <option value="60" selected>1 {{ __('publishing.hour') }}</option>
                    <option value="180">3 {{ __('publishing.hours') }}</option>
                    <option value="360">6 {{ __('publishing.hours') }}</option>
                    <option value="720">12 {{ __('publishing.hours') }}</option>
                    <option value="1440">24 {{ __('publishing.hours') }}</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.save_rss') }}
            </button>
        </form>
    </div>
</div>

{{-- Edit RSS Modal --}}
<div id="editRssModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-extrabold text-gray-900">{{ __('publishing.edit_rss') }}</h3>
            <button onclick="document.getElementById('editRssModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <form id="editRssForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('common.name') }}</label>
                <input type="text" id="editRssName" name="name" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.feed_url') }}</label>
                <input type="url" id="editRssUrl" name="feed_url" required class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.platforms') }}</label>
                <div class="space-y-1" id="editRssPlatforms">
                    @foreach(App\Models\WaSocialAccount::platforms() as $key => $name)
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="platform_targets[]" value="{{ $key }}" class="rounded text-brand-600 focus:ring-brand-500">
                        <span>{{ $name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">{{ __('publishing.check_interval') }}</label>
                <select id="editRssInterval" name="interval_minutes" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="15">15 {{ __('publishing.minutes') }}</option>
                    <option value="30">30 {{ __('publishing.minutes') }}</option>
                    <option value="60">1 {{ __('publishing.hour') }}</option>
                    <option value="180">3 {{ __('publishing.hours') }}</option>
                    <option value="360">6 {{ __('publishing.hours') }}</option>
                    <option value="720">12 {{ __('publishing.hours') }}</option>
                    <option value="1440">24 {{ __('publishing.hours') }}</option>
                </select>
            </div>
            <button type="submit" class="w-full bg-brand-600 text-white font-semibold py-2.5 rounded-xl hover:bg-brand-700 transition">
                {{ __('publishing.update_rss') }}
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editRss(id, name, url, platforms, interval) {
    document.getElementById('editRssForm').action = '/publishing/rss/' + id;
    document.getElementById('editRssName').value = name;
    document.getElementById('editRssUrl').value = url;
    document.getElementById('editRssInterval').value = interval;
    const checkboxes = document.querySelectorAll('#editRssPlatforms input[type=checkbox]');
    checkboxes.forEach(cb => { cb.checked = platforms.includes(cb.value); });
    document.getElementById('editRssModal').classList.remove('hidden');
}
</script>
@endpush
@stop
