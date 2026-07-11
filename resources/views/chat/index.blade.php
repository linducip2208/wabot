@extends('layouts.chat')
@section('title', 'Chat — WABot')

@section('chat_content')
<div x-data="chatApp({{ $activeContact?->id ?? 'null' }}, {{ $activeContact?->phone ? "'{$activeContact->phone}'" : 'null' }})" class="flex h-full"
    x-init="sessionsData = {{ json_encode($sessions->map(fn($s) => ['id' => $s->id, 'session_id' => $s->session_id, 'name' => $s->name, 'phone' => $s->phone])) }};
    contactsData = {{ json_encode($contacts->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'phone' => $c->phone, 'display_phone' => $c->display_phone, 'is_lid' => str_contains($c->phone, '@lid'), 'last_message' => $c->last_message, 'last_time' => $c->last_time?->format('H:i'), 'last_direction' => $c->last_direction, 'last_session_id' => $c->last_session_id])) }}">

    {{-- Contact List Sidebar --}}
    <aside class="w-full md:w-80 lg:w-96 flex-shrink-0 bg-gray-900 flex flex-col border-r border-gray-800"
        :class="activeContact ? 'hidden md:flex' : 'flex'">
        <div class="p-4 border-b border-gray-800">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-white font-bold text-lg">Chat</h2>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-xs text-gray-400">{{ $sessions->count() }} {{ __('common.online') }}</span>
                </div>
            </div>

            {{-- Session Tabs --}}
            <div class="flex gap-1 mb-3 overflow-x-auto pb-1" style="scrollbar-width:thin">
                <button @click="activeTab = 'all'"
                    class="flex-shrink-0 text-[11px] px-2.5 py-1.5 rounded-lg font-medium transition"
                    :class="activeTab === 'all' ? 'bg-brand-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'">
                    {{ __('common.all') }}
                </button>
                @foreach($sessions as $s)
                <button @click="activeTab = {{ $s->id }}"
                    class="flex-shrink-0 text-[11px] px-2.5 py-1.5 rounded-lg font-medium transition flex items-center gap-1"
                    :class="activeTab === {{ $s->id }} ? 'bg-brand-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ \Str::limit($s->name, 10) }}
                </button>
                @endforeach
            </div>

            <div class="relative">
                <input type="text" placeholder="{{ __('common.search') }} {{ __('common.contact') }}..." x-model="searchQuery"
                    class="w-full bg-gray-800 text-gray-200 text-sm rounded-xl py-2.5 pl-10 pr-4 border border-gray-700 focus:border-brand-500 focus:outline-none placeholder-gray-500 transition">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto" id="contactList">
            <template x-for="c in filteredContacts" :key="c.id">
                <div @click="openChat(c)"
                    class="flex items-start gap-3 px-4 py-3 cursor-pointer transition border-l-2"
                    :class="activeContact && activeContact.id === c.id
                        ? 'bg-gray-800 border-brand-500'
                        : 'border-transparent hover:bg-gray-800/50'">
                    <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-sm"
                        :style="'background-color: ' + avatarColor(c.name || c.phone)">
                        <span x-text="initials(c.name || c.phone)"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-white truncate" x-text="c.name || c.phone"></span>
                            <span class="text-[11px] text-gray-500 flex-shrink-0 ml-2" x-text="c.last_time"></span>
                        </div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-[11px] text-gray-500" x-text="c.display_phone" x-show="c.display_phone"></span>
                            <span @click.stop="openChat(c); $nextTick(() => showEditModal = true)" x-show="!c.display_phone && !c.is_lid" class="text-[11px] text-orange-400 cursor-pointer hover:underline">+ {{ __('chat.set_phone_number') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <template x-if="c.last_direction === 'out'">
                                <svg class="w-3 h-3 text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                            </template>
                            <span class="text-xs text-gray-400 truncate" x-text="c.last_message || '{{ __('chat.no_messages_yet') }}'"></span>
                        </div>
                    </div>
                </div>
            </template>
            <div x-show="filteredContacts.length === 0" class="px-4 py-16 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-gray-500 text-sm">{{ __('chat.no_conversations') }}</p>
                <p class="text-gray-600 text-xs mt-1">{{ __('chat.messages_will_appear') }}</p>
            </div>
        </nav>

        <div class="p-3 border-t border-gray-800 bg-gray-900/50">
            <a href="/" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm text-gray-400 hover:bg-gray-800 hover:text-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
        </div>
    </aside>

    {{-- Chat Panel --}}
    <main class="flex-1 flex flex-col bg-[#efeae2]"
        :class="activeContact ? 'flex' : 'hidden md:flex'">

        {{-- Empty State --}}
        <div x-show="!activeContact" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">WABot Omni-Channel</h3>
                <p class="text-gray-500 text-sm max-w-xs">{{ __('common.select') }} {{ __('common.contact') }} dari sidebar untuk mulai chat.<br>Auto-reply {{ __('common.active') }} untuk keyword yang cocok.</p>
            </div>
        </div>

        {{-- Active Chat --}}
        <template x-if="activeContact">
            <div class="flex-1 flex flex-col h-full">
                {{-- Chat Header --}}
                <div class="flex items-center gap-3 bg-[#f0f2f5] px-4 py-2.5 border-b border-gray-200">
                    <button @click="activeContact = null" class="md:hidden p-1 rounded-lg hover:bg-gray-200 transition">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                        :style="'background-color: ' + avatarColor(activeContact.name || activeContact.phone)">
                        <span x-text="initials(activeContact.name || activeContact.phone)"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-gray-900 text-sm" x-text="activeContact.name || activeContact.phone"></span>
                            <button @click.stop="showEditModal = true" class="p-0.5 rounded hover:bg-gray-200 transition flex-shrink-0" title="{{ __('common.edit') }} {{ __('common.contact') }}">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </div>
                        <span class="text-xs text-gray-500" x-text="activeContact.display_phone" x-show="activeContact.display_phone"></span>
                        <span x-show="!activeContact.display_phone && !activeContact.is_lid" @click="showEditModal = true" class="text-xs text-orange-500 cursor-pointer hover:underline">+ {{ __('chat.set_phone_number') }}</span>
                        <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                            <span x-show="sessionId">{{ __('chat.online_via') }} <span x-text="sessionName"></span></span>
                            <span x-show="!sessionId" class="text-red-500">{{ __('chat.no_session_connected') }}</span>
                            <template x-if="autoreplies.length > 0">
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-purple-100 text-purple-700">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z"/></svg>
                                    <span x-text="autoreplies.length"></span> auto-reply {{ __('common.active') }}
                                </span>
                            </template>
                        </div>
                    </div>
                    <select x-model="sessionId" class="text-xs border border-gray-300 rounded-lg py-1.5 px-2 bg-white text-gray-700 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="">{{ __('common.select') }} {{ __('common.session') }}</option>
                        <template x-for="s in sessions" :key="s.id">
                            <option :value="s.session_id" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                {{-- Messages Area --}}
                <div class="flex-1 overflow-y-auto px-4 md:px-12 py-4 space-y-1.5" id="messageArea"
                    style="background-color: #efeae2; background-image: url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%224%22 height=%224%22><rect width=%224%22 height=%224%22 fill=%22%23e8e2d7%22/></svg>');">
                    <template x-for="(msg, i) in messages" :key="msg.id">
                        <div class="flex"
                            :class="msg.direction === 'out' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[75%] md:max-w-[60%]">
                                <div class="px-3.5 py-2 rounded-lg text-sm leading-relaxed shadow-sm"
                                    :class="msg.direction === 'out'
                                        ? 'bg-[#d9fdd3] rounded-br-none'
                                        : 'bg-white rounded-bl-none'">
                                    <span x-text="msg.message"></span>
                                </div>
                                <div class="flex items-center gap-1.5 mt-0.5"
                                    :class="msg.direction === 'out' ? 'justify-end' : 'justify-start'">
                                    <span class="text-[10px] text-gray-500" x-text="msg.time"></span>
                                    <template x-if="msg.direction === 'out'">
                                        <svg class="w-3 h-3" :class="msg.{{ __('common.status') }} === 'sent' ? 'text-gray-400' : 'text-blue-500'" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </template>
                                </div>
                                <div x-show="i === 0 || messages[i-1].date !== msg.date" class="text-center mt-3 mb-2">
                                    <span class="text-[10px] bg-white/80 text-gray-500 px-3 py-0.5 rounded-full shadow-sm" x-text="msg.date"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="messages.length === 0" class="flex items-center justify-center h-full">
                        <p class="text-gray-500 text-sm">{{ __('chat.no_messages_in_chat') }}</p>
                    </div>
                </div>

                {{-- Input Bar --}}
                <div class="bg-[#f0f2f5] px-4 py-3 flex items-end gap-3 border-t border-gray-200">
                    <textarea x-model="newMessage" @keydown.enter.prevent="!$event.shiftKey && sendMessage()"
                        placeholder="{{ __('chat.type_message_placeholder') }}"
                        rows="1"
                        class="flex-1 resize-none bg-white rounded-xl py-2.5 px-4 text-sm border border-gray-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none placeholder-gray-400 max-h-32"
                        :disabled="!sessionId || sending"
                        @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 128) + 'px'"
                        style="min-height: 44px;"></textarea>
                    <button @click="sendMessage()" :disabled="!sessionId || sending || !newMessage.trim()"
                        class="w-11 h-11 flex-shrink-0 bg-brand-500 hover:bg-brand-600 disabled:bg-gray-300 text-white rounded-full flex items-center justify-center transition">
                        <svg x-show="!sending" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                        </svg>
                        <svg x-show="sending" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </main>
</div>

{{-- {{ __('common.edit') }} Contact Modal --}}
<div x-show="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <div class="absolute inset-0 bg-black/50" @click="showEditModal = false"></div>
    <div class="relative bg-white rounded-2xl p-6 w-full max-w-sm shadow-2xl" @click.stop="">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('common.edit') }} {{ __('common.contact') }}</h3>
        <div class="space-y-3">
            <div>
                <label class="text-xs font-medium text-gray-500">{{ __('common.name') }}</label>
                <input type="text" x-model="editName" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            <div>
                    <label class="text-xs font-medium text-gray-500">{{ __('chat.phone_display_label') }}</label>
                <input type="text" x-model="editPhone" placeholder="628xxxx" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <p class="text-[10px] text-gray-400 mt-1">{{ __('chat.phone_display_hint') }}</p>
            </div>
        </div>
        <div class="flex gap-2 mt-4">
            <button @click="showEditModal = false" class="flex-1 bg-gray-100 text-gray-700 rounded-xl py-2 text-sm font-medium hover:bg-gray-200">{{ __('common.cancel') }}</button>
            <button @click="saveContact()" class="flex-1 bg-brand-600 text-white rounded-xl py-2 text-sm font-semibold hover:bg-brand-700">{{ __('common.save') }}</button>
        </div>
    </div>
</div>
@endsection
