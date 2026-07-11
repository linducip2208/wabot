<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('widgets.index_title') }} — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

@extends('layouts.app')

@section('title', __('widgets.index_title'))
@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="widgetBuilder()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('widgets.heading') }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ __('widgets.subtitle') }}</p>
        </div>
        <button @click="openCreate()" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition flex items-center gap-2">
            <i class="fas fa-plus text-xs"></i> {{ __('widgets.create') }}
        </button>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-4 text-sm" x-data="{ show: true }" x-show="show" x-transition>
        {{ session('success') }}
    </div>
    @endif

    @if($widgets->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <div class="text-5xl mb-4">💬</div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ __('widgets.empty_title') }}</h3>
        <p class="text-gray-500 text-sm mb-6">{{ __('widgets.empty_subtitle') }}</p>
        <button @click="openCreate()" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
            {{ __('widgets.create') }}
        </button>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($widgets as $widget)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 card-lift">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm" style="background: {{ $widget->theme_color }}">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm">{{ $widget->name }}</h3>
                        <span class="text-xs {{ $widget->is_active ? 'text-green-600' : 'text-gray-400' }}">
                            <i class="fas fa-circle text-[6px]"></i> {{ $widget->is_active ? __('common.active') : __('common.inactive') }}
                        </span>
                    </div>
                </div>
                <span class="text-[10px] text-gray-400">{{ $widget->created_at->format('d M Y') }}</span>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">{{ __('widgets.embed_code') }}</span>
                    <button onclick="copyEmbed('{{ $widget->embed_key }}')" class="text-indigo-600 text-xs hover:underline flex items-center gap-1">
                        <i class="fas fa-copy text-[10px]"></i> {{ __('common.copy') }}
                    </button>
                </div>
                <code class="text-[11px] text-gray-700 break-all block select-all">&lt;script src="{{ url('/widget/' . $widget->embed_key . '.js') }}"&gt;&lt;/script&gt;</code>
            </div>

            <div class="flex items-center gap-2">
                <button @click="openEdit({{ $widget->id }})" class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-lg text-xs font-medium hover:bg-gray-200 transition flex items-center justify-center gap-1">
                    <i class="fas fa-pen text-[10px]"></i> {{ __('common.edit') }}
                </button>
                <form method="POST" action="{{ route('widgets.destroy', $widget) }}" onsubmit="return confirm('{{ __('common.confirm_delete') }}')" class="flex-1">
                    @csrf @method('DELETE')
                    <button class="w-full bg-red-50 text-red-600 px-3 py-2 rounded-lg text-xs font-medium hover:bg-red-100 transition flex items-center justify-center gap-1">
                        <i class="fas fa-trash text-[10px]"></i> {{ __('common.delete') }}
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal Create/Edit --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
        <div class="absolute inset-0 bg-black/50" @click="modalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" @click.outside="modalOpen = false">
            <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 flex items-center justify-between z-10">
                <h2 class="text-lg font-bold text-gray-900" x-text="editingId ? '{{ __('widgets.edit_widget') }}' : '{{ __('widgets.create_widget') }}'"></h2>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form method="POST" :action="editingId ? '{{ url('widgets') }}/' + editingId : '{{ route('widgets.store') }}'" class="p-6">
                @csrf
                <template x-if="editingId">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Left: Settings --}}
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('common.name') }} *</label>
                            <input type="text" name="name" x-model="form.name" required maxlength="255"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition"
                                placeholder="{{ __('widgets.name_placeholder') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('widgets.greeting_message') }}</label>
                            <textarea name="greeting_message" x-model="form.greeting_message" rows="2" maxlength="500"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition"
                                placeholder="{{ __('widgets.greeting_placeholder') }}"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('widgets.offline_message') }}</label>
                            <textarea name="offline_message" x-model="form.offline_message" rows="2" maxlength="500"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition"
                                placeholder="{{ __('widgets.offline_placeholder') }}"></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('widgets.theme_color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" name="theme_color" x-model="form.theme_color"
                                        class="w-10 h-10 rounded-lg border cursor-pointer">
                                    <input type="text" x-model="form.theme_color" maxlength="7"
                                        class="flex-1 border border-gray-300 rounded-xl px-3 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('widgets.position') }}</label>
                                <select name="position" x-model="form.position"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition">
                                    <option value="bottom-right">{{ __('widgets.bottom_right') }}</option>
                                    <option value="bottom-left">{{ __('widgets.bottom_left') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('widgets.button_icon') }}</label>
                            <select name="button_icon" x-model="form.button_icon"
                                class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition">
                                <option value="chat">💬 Chat</option>
                                <option value="headset">🎧 Headset</option>
                                <option value="question">❓ Question</option>
                                <option value="message">✉️ Message</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                {{ __('widgets.channels') }}
                                <span class="text-gray-400 font-normal text-xs">({{ __('common.optional') }})</span>
                            </label>
                            <input type="hidden" name="channels" :value="JSON.stringify(form.channels)">
                            <div class="space-y-2">
                                @php
                                    $allChannels = App\Services\ChannelRegistry::all();
                                    $availChannels = [];
                                    foreach ($allChannels as $key => $cfg) {
                                        if (!empty($connectedAccounts[$key])) {
                                            $availChannels[] = ['type' => $key, 'label' => $cfg['label'], 'accounts' => $connectedAccounts[$key]];
                                        }
                                    }
                                @endphp
                                @if(empty($availChannels))
                                <p class="text-xs text-amber-600">{{ __('widgets.no_connected_accounts') }}</p>
                                @else
                                @foreach($availChannels as $ch)
                                @php $chJson = json_encode(['type' => $ch['type'], 'label' => $ch['label']]); @endphp
                                <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:border-indigo-300 transition cursor-pointer"
                                    @click="toggleChannel({{ $chJson }})">
                                    <input type="checkbox" :checked="hasChannel('{{ $ch['type'] }}')" class="w-4 h-4 rounded text-indigo-600 pointer-events-none">
                                    <span class="text-sm font-medium text-gray-700">{{ $ch['label'] }}</span>
                                    @if(is_array($ch['accounts']) && count($ch['accounts']) > 1)
                                    <select x-show="hasChannel('{{ $ch['type'] }}')"
                                        @change="setChannelId('{{ $ch['type'] }}', $el.value)"
                                        class="ml-auto w-48 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition">
                                        <option value="">{{ __('widgets.select_account') }}</option>
                                        @foreach($ch['accounts'] as $acc)
                                        <option value="{{ $acc['id'] }}">{{ $acc['name'] ?? ($acc['bot_username'] ?? ($acc['username'] ?? ($acc['page_id'] ?? ($acc['phone_number'] ?? (is_string($acc) ? $acc : ''))))) }}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    <input type="text" x-show="hasChannel('{{ $ch['type'] }}')" x-model="getChannel('{{ $ch['type'] }}').id"
                                        class="ml-auto w-48 border border-gray-300 rounded-lg px-3 py-1.5 text-xs focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 outline-none transition"
                                        value="{{ is_array($ch['accounts']) && isset($ch['accounts'][0]) ? ($ch['accounts'][0]['id'] ?? '') : '' }}"
                                        readonly>
                                    @endif
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="hidden" name="is_active" :value="form.is_active ? '1' : '0'">
                            <button type="button" @click="form.is_active = !form.is_active"
                                class="relative w-11 h-6 rounded-full transition-colors duration-200"
                                :class="form.is_active ? 'bg-indigo-600' : 'bg-gray-300'">
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                    :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"></span>
                            </button>
                            <span class="text-sm text-gray-700 select-none">{{ __('common.active') }}</span>
                        </div>
                    </div>

                    {{-- Right: Live Preview --}}
                    <div class="bg-gray-100 rounded-2xl border border-gray-200 relative overflow-hidden" style="min-height: 400px;">
                        <div class="absolute top-2 left-3 right-3 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                            <span class="ml-2 bg-white/80 text-[9px] text-gray-500 px-3 py-0.5 rounded-full">your-website.com</span>
                        </div>
                        <div class="absolute top-8 left-0 right-0 bottom-0 flex flex-col items-center justify-center p-4">
                            <div class="text-center mb-4">
                                <span class="text-4xl">🌐</span>
                                <p class="text-sm text-gray-500 mt-2 font-medium">{{ __('widgets.preview_website') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('widgets.preview_hint') }}</p>
                            </div>
                            <div class="absolute"
                                :class="form.position === 'bottom-left' ? 'bottom-4 left-4' : 'bottom-4 right-4'">
                                <div class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-white text-xl cursor-pointer animate-pulse"
                                    :style="{ background: form.theme_color }">
                                    <span x-show="form.button_icon === 'chat'">💬</span>
                                    <span x-show="form.button_icon === 'headset'">🎧</span>
                                    <span x-show="form.button_icon === 'question'">❓</span>
                                    <span x-show="form.button_icon === 'message'">✉️</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" @click="modalOpen = false" class="text-gray-600 px-4 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-100 transition">
                        {{ __('common.cancel') }}
                    </button>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
                        {{ __('common.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function widgetBuilder() {
            return {
                modalOpen: false,
                editingId: null,
                form: {
                    name: '',
                    greeting_message: '',
                    offline_message: '',
                    theme_color: '#6366f1',
                    position: 'bottom-right',
                    button_icon: 'chat',
                    channels: [],
                    is_active: true,
                },
                toggleChannel(ch) {
                    const idx = this.form.channels.findIndex(c => c.type === ch.type);
                    if (idx >= 0) {
                        this.form.channels.splice(idx, 1);
                    } else {
                        this.form.channels.push({ type: ch.type, id: '', label: ch.label });
                    }
                },
                hasChannel(type) {
                    return this.form.channels.some(c => c.type === type);
                },
                getChannel(type) {
                    return this.form.channels.find(c => c.type === type) || { id: '' };
                },
                setChannelId(type, id) {
                    const ch = this.form.channels.find(c => c.type === type);
                    if (ch) ch.id = id;
                },
                resetForm() {
                    this.form = {
                        name: '',
                        greeting_message: '',
                        offline_message: '',
                        theme_color: '#6366f1',
                        position: 'bottom-right',
                        button_icon: 'chat',
                        channels: [],
                        is_active: true,
                    };
                    this.editingId = null;
                },
                openCreate() {
                    this.resetForm();
                    this.modalOpen = true;
                },
                openEdit(id) {
                    const data = JSON.parse(document.getElementById('widget-data-' + id).textContent);
                    this.form = {
                        name: data.name,
                        greeting_message: data.greeting_message || '',
                        offline_message: data.offline_message || '',
                        theme_color: data.theme_color,
                        position: data.position,
                        button_icon: data.button_icon,
                        channels: data.channels || [],
                        is_active: data.is_active,
                    };
                    this.editingId = id;
                    this.modalOpen = true;
                },
            }
        }

        function copyEmbed(key) {
            const code = '<script src="{{ url('/') }}/widget/' + key + '.js"><\/script>';
            navigator.clipboard.writeText(code).then(() => {
                alert('{{ __('widgets.embed_copied') }}');
            });
        }
    </script>

    @foreach($widgets as $widget)
    <script type="application/json" id="widget-data-{{ $widget->id }}">@json($widget->only(['name', 'greeting_message', 'offline_message', 'theme_color', 'position', 'button_icon', 'channels', 'is_active']))</script>
    @endforeach
</div>
@endsection

</body>
</html>
