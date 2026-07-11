@extends('layouts.app')
@section('title', __('aistudio.content_title'))
@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-extrabold text-gray-900">{{ __('aistudio.content_title') }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('aistudio.content_subtitle') }}</p>
    </div>
    <a href="{{ route('ai-content.templates') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition flex items-center gap-2">
        <i class="fas fa-layer-group text-xs"></i> {{ __('aistudio.manage_templates') }}
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    {{-- Left: Input Panel --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-pen-to-square text-brand-500"></i> {{ __('aistudio.prompt_input') }}
            </h2>
            <form method="POST" action="{{ route('ai-content.generate') }}" id="contentForm">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('aistudio.your_prompt') }} <span class="text-red-400">*</span></label>
                        <textarea name="prompt" rows="4" required placeholder="{{ __('aistudio.prompt_placeholder') }}" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-200 focus:border-brand-400">{{ old('prompt') }}</textarea>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('aistudio.platform') }}</label>
                        <select name="platform" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="general">{{ __('aistudio.platform_general') }}</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                            <option value="twitter">X / Twitter</option>
                            <option value="telegram">Telegram</option>
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('aistudio.tone') }}</label>
                            <select name="tone" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                                <option value="professional">{{ __('aistudio.tone_professional') }}</option>
                                <option value="casual">{{ __('aistudio.tone_casual') }}</option>
                                <option value="humorous">{{ __('aistudio.tone_humorous') }}</option>
                                <option value="persuasive">{{ __('aistudio.tone_persuasive') }}</option>
                                <option value="urgent">{{ __('aistudio.tone_urgent') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500">{{ __('aistudio.length') }}</label>
                            <select name="length" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                                <option value="short">{{ __('aistudio.length_short') }}</option>
                                <option value="medium" selected>{{ __('aistudio.length_medium') }}</option>
                                <option value="long">{{ __('aistudio.length_long') }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('aistudio.language') }}</label>
                        <select name="language" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="id">Bahasa Indonesia</option>
                            <option value="en">English</option>
                            <option value="auto">{{ __('aistudio.language_auto') }}</option>
                        </select>
                    </div>

                    @if($templates->count())
                    <div>
                        <label class="text-xs font-medium text-gray-500">{{ __('aistudio.use_template') }}</label>
                        <select name="template_id" class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm">
                            <option value="">{{ __('aistudio.no_template') }}</option>
                            @foreach($templates as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }} @if($tpl->user_id !== Auth::id()) ({{ __('aistudio.public') }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <button type="submit" class="w-full bg-gradient-to-r from-brand-600 to-brand-700 text-white px-4 py-3 rounded-xl text-sm font-semibold hover:from-brand-700 hover:to-brand-800 transition flex items-center justify-center gap-2 card-lift" id="generateBtn">
                        <i class="fas fa-wand-magic-sparkles"></i> {{ __('aistudio.generate_content') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Template Quick Picks --}}
        @if($templates->where('user_id', Auth::id())->count())
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-bookmark text-brand-500"></i> {{ __('aistudio.your_templates') }}
            </h3>
            <div class="space-y-2">
                @foreach($templates->where('user_id', Auth::id())->take(5) as $tpl)
                    <button onclick="document.querySelector('select[name=template_id]').value='{{ $tpl->id }}'; document.querySelector('textarea[name=prompt]').focus()" class="w-full text-left p-2.5 rounded-lg border border-gray-100 hover:border-brand-200 hover:bg-brand-50/30 transition text-sm">
                        <div class="font-medium text-gray-800 text-xs">{{ $tpl->name }}</div>
                        <div class="text-[11px] text-gray-400 mt-0.5 truncate">{{ Str::limit($tpl->prompt_template, 60) }}</div>
                    </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Right: Output Panel --}}
    <div class="lg:col-span-2 space-y-4">
        @if(session('generated_content'))
        <div class="bg-white rounded-xl border border-brand-200 shadow-sm p-5" x-data="{ copied: false }">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center"><i class="fas fa-wand-magic-sparkles text-brand-500 text-xs"></i></div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm">{{ __('aistudio.generated_content') }}</div>
                        <div class="text-[11px] text-gray-400">{{ __('aistudio.based_on_prompt') }}: {{ Str::limit(session('generated_prompt'), 80) }}</div>
                    </div>
                </div>
                <button @click="navigator.clipboard.writeText(`{{ addslashes(session('generated_content')) }}`); copied = true; setTimeout(() => copied = false, 2000)" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-brand-600 transition relative">
                    <i class="fas" :class="copied ? 'fa-check text-green-500' : 'fa-copy'"></i>
                    <span x-show="copied" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-[10px] px-2 py-0.5 rounded whitespace-nowrap" x-cloak>Copied!</span>
                </button>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 max-h-[500px] overflow-y-auto">
                <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{!! nl2br(e(session('generated_content'))) !!}</div>
            </div>
        </div>
        @elseif(session('success') && !session('generated_content'))
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4"><i class="fas fa-wand-magic-sparkles text-2xl text-gray-400"></i></div>
            <p class="text-gray-500 font-medium">{{ __('aistudio.ready_to_generate') }}</p>
            <p class="text-sm text-gray-400 mt-1">{{ __('aistudio.fill_prompt_left') }}</p>
        </div>
        @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 rounded-2xl bg-brand-50 flex items-center justify-center mx-auto mb-5">
                <i class="fas fa-wand-magic-sparkles text-3xl text-brand-400"></i>
            </div>
            <h3 class="text-lg font-extrabold text-gray-900 mb-2">{{ __('aistudio.ai_content_studio') }}</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto">{{ __('aistudio.studio_description') }}</p>
        </div>
        @endif

        {{-- History --}}
        @php $history = session('ai_content_history', []); @endphp
        @if(count($history) > 0)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2">
                <i class="fas fa-clock-rotate-left text-gray-400"></i> {{ __('aistudio.recent_generated') }}
            </h3>
            <div class="space-y-3 max-h-[400px] overflow-y-auto">
                @foreach($history as $idx => $item)
                <div class="p-3 rounded-lg border border-gray-100 hover:border-gray-200 transition">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-medium text-gray-400">{{ $item['platform'] ?? 'general' }} &middot; {{ $item['tone'] ?? 'professional' }} &middot; <span x-data x-text="new Date('{{ $item['created_at'] }}').toLocaleString('id-ID')" class="text-[11px]"></span></span>
                        <button onclick="navigator.clipboard.writeText(`{{ addslashes($item['result']) }}`)" class="text-gray-400 hover:text-brand-600 transition text-xs"><i class="fas fa-copy"></i></button>
                    </div>
                    <p class="text-xs text-gray-600 line-clamp-2">{{ Str::limit($item['result'], 150) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
