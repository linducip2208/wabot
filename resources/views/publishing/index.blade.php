@extends('layouts.app')

@section('title', __('publishing.composer_title') . ' — ' . config('app.name'))

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-extrabold text-gray-900"><i class="fas fa-pen-to-square text-brand-500 mr-2"></i>{{ __('publishing.composer') }}</h1>
    <p class="text-gray-500 text-sm mt-1">{{ __('publishing.composer_subtitle') }}</p>
</div>

@if($accountsCount === 0)
<div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-6 mb-6">
    <div class="flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 text-xl mt-0.5"></i>
        <div>
            <h3 class="font-semibold mb-1">{{ __('publishing.no_accounts_title') }}</h3>
            <p class="text-sm">{{ __('publishing.no_accounts_desc') }}</p>
        </div>
    </div>
</div>
@endif

<form action="{{ route('publishing.store') }}" method="POST" class="grid lg:grid-cols-3 gap-6">
    @csrf
    <input type="hidden" name="action" value="draft" id="formAction">

    {{-- Left: Composer --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Content --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('publishing.content') }}</label>
            <textarea name="content" rows="6" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none" placeholder="{{ __('publishing.content_placeholder') }}">{{ old('content') }}</textarea>
            <div class="mt-3 flex items-center gap-4">
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="text-xs text-brand-600 hover:text-brand-700 font-medium flex items-center gap-1">
                        <i class="fas fa-book-open"></i> {{ __('publishing.use_caption') }}
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute top-8 left-0 bg-white border border-gray-200 rounded-xl shadow-lg z-10 w-72 max-h-48 overflow-y-auto">
                        @forelse($captions as $caption)
                        <button type="button" @click="$el.closest('form').querySelector('textarea[name=content]').value = `{{ str_replace('`', '\`', addslashes($caption->content)) }}`; open = false" class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-0">
                            <div class="font-medium text-gray-800">{{ $caption->name }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ Str::limit($caption->content, 60) }}</div>
                        </button>
                        @empty
                        <div class="px-4 py-3 text-sm text-gray-500">{{ __('publishing.no_captions') }}</div>
                        @endforelse
                    </div>
                </div>
                <span class="text-xs text-gray-400">{{ __('publishing.spintax_hint') }}</span>
            </div>
        </div>

        {{-- Media URLs --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{ urls: [], newUrl: '' }">
            <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('publishing.media_urls') }}</label>
            <div class="flex gap-2 mb-3">
                <input type="url" x-model="newUrl" @keydown.enter.prevent="if(newUrl.trim()){ urls.push(newUrl.trim()); newUrl = '' }" class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" placeholder="{{ __('publishing.media_url_placeholder') }}">
                <button type="button" @click="if(newUrl.trim()){ urls.push(newUrl.trim()); newUrl = '' }" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-200 transition">{{ __('publishing.add') }}</button>
            </div>
            <template x-if="urls.length > 0">
                <div class="space-y-2">
                    <template x-for="(url, idx) in urls" :key="idx">
                        <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2">
                            <span class="flex-1 text-sm text-gray-700 truncate" x-text="url"></span>
                            <button type="button" @click="urls.splice(idx, 1)" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                            <input type="hidden" name="media_urls[]" :value="url">
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Recent Posts --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">{{ __('publishing.recent_posts') }}</h3>
            @forelse($recentPosts as $rp)
            <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800 truncate">{{ Str::limit($rp->content, 80) ?: __('publishing.no_content') }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $rp->status === 'published' ? 'bg-green-100 text-green-700' : ($rp->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : ($rp->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                            {{ __('common.' . $rp->status) }}
                        </span>
                        <span class="text-xs text-gray-400">{{ $rp->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                @if(!$rp->isPublished())
                <form action="{{ route('publishing.destroy', $rp) }}" method="POST" class="ml-2" onsubmit="return confirm('{{ __('publishing.delete_confirm') }}')">
                    @csrf @method('DELETE')
                    <button class="text-gray-400 hover:text-red-500 text-sm"><i class="fas fa-trash"></i></button>
                </form>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-500">{{ __('publishing.no_posts_yet') }}</p>
            @endforelse
        </div>
    </div>

    {{-- Right: Settings --}}
    <div class="space-y-6">
        {{-- Platforms --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <label class="block text-sm font-semibold text-gray-700 mb-3">{{ __('publishing.platforms') }}</label>
            <div class="space-y-2">
                @foreach(App\Models\WaSocialAccount::platforms() as $key => $name)
                @php $hasAccount = $accounts->where('platform', $key)->count() > 0; @endphp
                <label class="flex items-center gap-3 p-2.5 rounded-xl border {{ $hasAccount ? 'border-gray-200 hover:bg-gray-50' : 'border-gray-100 bg-gray-50 opacity-50' }} cursor-pointer transition">
                    <input type="checkbox" name="platform_targets[]" value="{{ $key }}" {{ $hasAccount ? '' : 'disabled' }} class="rounded text-brand-600 focus:ring-brand-500">
                    <i class="{{ $key === 'facebook_page' ? 'fab fa-facebook text-blue-600' : ($key === 'instagram_professional' ? 'fab fa-instagram text-pink-600' : ($key === 'x_twitter' ? 'fab fa-x-twitter text-gray-800' : ($key === 'tiktok' ? 'fab fa-tiktok text-gray-800' : 'fab fa-linkedin text-blue-700'))) }} w-5 text-center"></i>
                    <div>
                        <div class="text-sm font-medium text-gray-800">{{ $name }}</div>
                        @if(!$hasAccount)
                        <div class="text-[11px] text-gray-400">{{ __('publishing.no_connected_account') }}</div>
                        @endif
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Schedule --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6" x-data="{ schedule: false }">
            <label class="flex items-center gap-2 mb-3">
                <input type="checkbox" x-model="schedule" class="rounded text-brand-600 focus:ring-brand-500">
                <span class="text-sm font-semibold text-gray-700">{{ __('publishing.schedule_post') }}</span>
            </label>
            <div x-show="schedule" class="mt-3">
                <input type="datetime-local" name="scheduled_at" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <p class="text-xs text-gray-400 mt-1">{{ __('publishing.schedule_hint') }}</p>
            </div>
        </div>

        {{-- Campaign & Label --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('publishing.campaign') }}</label>
                <select name="campaign_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">{{ __('publishing.no_campaign') }}</option>
                    @foreach($campaigns as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">{{ __('publishing.label') }}</label>
                <select name="label_id" class="w-full border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="">{{ __('publishing.no_label') }}</option>
                    @foreach($labels as $l)
                    <option value="{{ $l->id }}">
                        <span class="inline-block w-2.5 h-2.5 rounded-full mr-2" style="background:{{ $l->color }}"></span>
                        {{ $l->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col gap-2">
            <button type="submit" onclick="document.getElementById('formAction').value='publish'" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white font-semibold py-3 rounded-xl hover:shadow-lg hover:from-brand-700 hover:to-brand-800 transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> {{ __('publishing.publish_now') }}
            </button>
            <button type="submit" onclick="document.getElementById('formAction').value='schedule'" class="w-full bg-blue-50 text-blue-700 font-semibold py-3 rounded-xl border border-blue-200 hover:bg-blue-100 transition flex items-center justify-center gap-2">
                <i class="fas fa-clock"></i> {{ __('publishing.schedule') }}
            </button>
            <button type="submit" onclick="document.getElementById('formAction').value='draft'" class="w-full bg-gray-50 text-gray-700 font-semibold py-3 rounded-xl border border-gray-200 hover:bg-gray-100 transition flex items-center justify-center gap-2">
                <i class="fas fa-file-lines"></i> {{ __('publishing.save_draft') }}
            </button>
        </div>
    </div>
</form>
@stop
