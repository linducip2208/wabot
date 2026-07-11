<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if(session('language_rtl')) dir="rtl" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name')) — WhatsApp SaaS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.3.2/css/flag-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('styles')
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        :root { --sidebar-width: 260px; }
        .sidebar { width: var(--sidebar-width); transition: transform .3s ease; }
        @media (max-width: 1023px) {
            .sidebar { position: fixed; top: 0; left: 0; bottom: 0; z-index: 50; transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay { display: none; }
            .sidebar-overlay.show { display: block; }
        }
        .nav-group-header { cursor: pointer; user-select: none; }
        .nav-group-header .chevron { transition: transform .2s ease; }
        .nav-group-header.open .chevron { transform: rotate(90deg); }
        .nav-group-body { overflow: hidden; transition: max-height .3s ease; }
        .nav-link { transition: all .15s ease; }
        .nav-link:hover { background: rgba(255,255,255,.06); padding-left: 1.25rem; }
        .nav-link.active { background: rgba(59,130,246,.15); color: #60a5fa; border-left: 3px solid #3b82f6; }
        .topbar { backdrop-filter: blur(12px) saturate(180%); background: rgba(255,255,255,.8); }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                        sidebar: { bg: '#1e293b', hover: '#334155', active: '#1e3a5f' },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

@auth
<div class="flex min-h-screen">
{{-- Mobile Overlay --}}
<div class="sidebar-overlay fixed inset-0 bg-black/50 z-40 lg:hidden"
    :class="sidebarOpen && 'show'" @click="sidebarOpen = false"></div>

{{-- Sidebar --}}
<aside class="sidebar bg-sidebar-bg flex flex-col h-screen fixed lg:sticky top-0 left-0 z-50"
    :class="sidebarOpen && 'open'">
    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 flex-shrink-0">
        <i class="fas fa-paper-plane text-brand-400 text-lg"></i>
        <span class="text-white font-extrabold text-lg tracking-tight">{{ config('app.name') }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-3 px-3 space-y-1">
        @php $langs = \App\Models\Language::active()->ordered()->get(); $cur = app()->getLocale(); @endphp

        {{-- OVERVIEW --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.overview') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 200px;">
            <a href="{{ route('dashboard.stats') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie w-4 text-center"></i> {{ __('sidebar.dashboard') }}
            </a>
            <a href="{{ route('logger.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('logger*') ? 'active' : '' }}">
                <i class="fas fa-history w-4 text-center"></i> {{ __('sidebar.activity_feed') }}
            </a>
        </div>

        {{-- WHATSAPP --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open')">
            <span>WhatsApp</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 500px;">
            <a href="{{ route('sessions.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sessions*') ? 'active' : '' }}">
                <i class="fab fa-whatsapp w-4 text-center"></i> Sessions / Agents
            </a>
        </div>

        {{-- APPOINTMENTS --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.appointments') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 200px;">
            <a href="{{ route('appointments.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('appointments*') ? 'active' : '' }}">
                <i class="fas fa-calendar-check w-4 text-center"></i> {{ __('sidebar.appointments') }}
            </a>
        </div>

        {{-- INBOX --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 open" x-data="{ open: true }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.inbox') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 500px;">
            <a href="{{ route('chat.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('chat*') || request()->is('/') && !request()->is('dashboard*') ? 'active' : '' }}">
                <i class="fas fa-comments w-4 text-center"></i> {{ __('sidebar.live_chat') }}
            </a>
            <a href="{{ route('messages.received') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('messages/received*') ? 'active' : '' }}">
                <i class="fas fa-inbox w-4 text-center"></i> {{ __('sidebar.conversations') }}
            </a>
            <a href="{{ route('messages.queue') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('messages/queue*') ? 'active' : '' }}">
                <i class="fas fa-clock w-4 text-center"></i> {{ __('sidebar.queue') }}
            </a>
            <a href="{{ route('messages.sent') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('messages/sent*') ? 'active' : '' }}">
                <i class="fas fa-check-double w-4 text-center"></i> {{ __('sidebar.sent_messages') }}
            </a>
            <a href="{{ route('contacts.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('contacts*') ? 'active' : '' }}">
                <i class="fas fa-address-book w-4 text-center"></i> {{ __('sidebar.contacts') }}
            </a>
            <a href="{{ route('groups.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('groups*') ? 'active' : '' }}">
                <i class="fas fa-layer-group w-4 text-center"></i> {{ __('sidebar.groups') }}
            </a>
        </div>

        {{-- MARKETING --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.marketing') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 400px;' : 'max-height: 0;'">
            <a href="{{ route('messages.send.form') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('messages/send*') ? 'active' : '' }}">
                <i class="fas fa-paper-plane w-4 text-center"></i> {{ __('sidebar.broadcast') }}
            </a>
            <a href="{{ route('campaigns.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('campaigns*') ? 'active' : '' }}">
                <i class="fas fa-bullhorn w-4 text-center"></i> {{ __('sidebar.campaigns') }}
            </a>
            <a href="{{ route('recurrings.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('recurrings*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt w-4 text-center"></i> {{ __('sidebar.scheduled_messages') }}
            </a>
            <a href="{{ route('drips.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('drips*') ? 'active' : '' }}">
                <i class="fas fa-water w-4 text-center"></i> {{ __('sidebar.drip_campaign') }}
            </a>
            <a href="{{ route('ab-tests.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('ab-tests*') ? 'active' : '' }}">
                <i class="fas fa-flask w-4 text-center"></i> {{ __('sidebar.ab_testing') }}
            </a>
            <a href="{{ route('click-stats.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('click-stats*') ? 'active' : '' }}">
                <i class="fas fa-mouse-pointer w-4 text-center"></i> {{ __('sidebar.click_tracking') }}
            </a>
            <a href="{{ route('templates.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('templates*') ? 'active' : '' }}">
                <i class="fas fa-file-lines w-4 text-center"></i> {{ __('sidebar.templates') }}
            </a>
        </div>

        {{-- AI AUTOMATION --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.ai_automation') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 400px;' : 'max-height: 0;'">
            <a href="{{ route('ai-agents.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('ai-agents*') ? 'active' : '' }}">
                <i class="fas fa-robot w-4 text-center"></i> {{ __('sidebar.ai_agents') }}
            </a>
            <a href="{{ route('intents.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('intents*') ? 'active' : '' }}">
                <i class="fas fa-brain w-4 text-center"></i> {{ __('sidebar.intent') }}
            </a>
            <a href="{{ route('autoreplies.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('autoreplies*') ? 'active' : '' }}">
                <i class="fas fa-reply w-4 text-center"></i> {{ __('sidebar.auto_reply') }}
            </a>
            <a href="{{ route('knowledge.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('knowledge*') ? 'active' : '' }}">
                <i class="fas fa-database w-4 text-center"></i> {{ __('sidebar.knowledge_base') }}
            </a>
            <a href="{{ route('ai-keys.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('ai-keys*') ? 'active' : '' }}">
                <i class="fas fa-key w-4 text-center"></i> {{ __('sidebar.ai_api_keys') }}
            </a>
        </div>

        {{-- COMMERCE --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.commerce') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="{{ route('catalogs.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('catalogs*') ? 'active' : '' }}">
                <i class="fas fa-shopping-bag w-4 text-center"></i> {{ __('sidebar.catalog') }}
                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="{{ route('commerce.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('commerce*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart w-4 text-center"></i> {{ __('sidebar.orders') }}
            </a>
            <a href="{{ route('buttons.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('buttons*') ? 'active' : '' }}">
                <i class="fas fa-hand-pointer w-4 text-center"></i> {{ __('sidebar.buttons') }}
            </a>
            <a href="{{ route('forms.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('forms*') ? 'active' : '' }}">
                <i class="fas fa-wpforms w-4 text-center"></i> {{ __('sidebar.forms') }}
                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="{{ route('media-templates.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('media-templates*') ? 'active' : '' }}">
                <i class="fas fa-photo-video w-4 text-center"></i> {{ __('sidebar.media') }}
            </a>
        </div>

        {{-- CRM --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.crm') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="{{ route('deals.board') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('deals-board*') ? 'active' : '' }}">
                <i class="fas fa-funnel-dollar w-4 text-center"></i> {{ __('sidebar.pipeline') }}
            </a>
            <a href="{{ route('deals.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('deals') || request()->is('deals/*') && !request()->is('deals-board*') ? 'active' : '' }}">
                <i class="fas fa-handshake w-4 text-center"></i> {{ __('sidebar.deals') }}
            </a>
            <a href="{{ route('kanban.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('kanban*') ? 'active' : '' }}">
                <i class="fas fa-columns w-4 text-center"></i> {{ __('sidebar.kanban') }}
            </a>
            <a href="{{ route('contact-tags.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('contact-tags*') ? 'active' : '' }}">
                <i class="fas fa-tags w-4 text-center"></i> {{ __('sidebar.tags') }}
            </a>
        </div>

    {{-- CHANNELS --}}
    <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
        <span>{{ __('sidebar.channels') }}</span>
        <i class="fas fa-chevron-right text-[9px] chevron"></i>
    </div>
    <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 600px;' : 'max-height: 0;'">
            <a href="{{ route('meta.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('meta*') ? 'active' : '' }}">
                <i class="fab fa-meta w-4 text-center"></i> {{ __('sidebar.whatsapp_cloud_api') }}
            </a>
            <a href="{{ route('calls.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('calls*') ? 'active' : '' }}">
                <i class="fas fa-phone-volume w-4 text-center"></i> {{ __('sidebar.whatsapp_calling') }}
                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="{{ route('instagram.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('instagram*') ? 'active' : '' }}">
                <i class="fab fa-instagram w-4 text-center"></i> {{ __('sidebar.instagram') }}
                <span class="ml-auto text-[9px] text-blue-400 border border-blue-400/40 rounded px-1.5 py-0">Meta</span>
            </a>
            <a href="{{ route('webhooks.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('webhooks*') ? 'active' : '' }}">
                <i class="fas fa-bolt w-4 text-center"></i> {{ __('sidebar.webhooks') }}
            </a>
            <a href="{{ route('telegram.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('telegram*') ? 'active' : '' }}">
                <i class="fab fa-telegram w-4 text-center"></i> {{ __('sidebar.telegram') }}
            </a>
            <a href="{{ route('twilio.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('twilio*') ? 'active' : '' }}">
                <i class="fas fa-sms w-4 text-center"></i> {{ __('sidebar.sms') }}
            </a>
            <a href="{{ route('sendgrid.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sendgrid*') ? 'active' : '' }}">
                <i class="fas fa-envelope w-4 text-center"></i> {{ __('sidebar.email') }}
            </a>
            <a href="{{ route('facebook.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('facebook*') ? 'active' : '' }}">
                <i class="fab fa-facebook w-4 text-center"></i> {{ __('sidebar.facebook') }}
            </a>
            <a href="{{ route('gbm.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('gbm*') ? 'active' : '' }}">
                <i class="fab fa-google w-4 text-center"></i> {{ __('sidebar.gbm') }}
            </a>
            <a href="{{ route('discord.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('discord*') ? 'active' : '' }}">
                <i class="fab fa-discord w-4 text-center"></i> {{ __('sidebar.discord') }}
            </a>
            <a href="{{ route('tiktok.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('tiktok*') ? 'active' : '' }}">
                <i class="fab fa-tiktok w-4 text-center"></i> {{ __('sidebar.tiktok') }}
            </a>
            <a href="{{ route('line.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('line*') ? 'active' : '' }}">
                <i class="fab fa-line w-4 text-center"></i> {{ __('sidebar.line') }}
            </a>
            <a href="{{ route('twitter.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('twitter*') ? 'active' : '' }}">
                <i class="fab fa-x-twitter w-4 text-center"></i> {{ __('sidebar.twitter') }}
            </a>
            <a href="{{ route('widgets.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('widgets*') ? 'active' : '' }}">
                <i class="fas fa-puzzle-piece w-4 text-center"></i> {{ __('sidebar.widgets') }}
            </a>
        </div>

        {{-- TEAM --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.team') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="{{ route('inbox.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('inbox*') ? 'active' : '' }}">
                <i class="fas fa-inbox w-4 text-center"></i> {{ __('sidebar.shared_inbox') }}
            </a>
            <a href="{{ route('team-members.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('team-members*') ? 'active' : '' }}">
                <i class="fas fa-users w-4 text-center"></i> {{ __('sidebar.members') }}
            </a>
            <a href="{{ route('sla.dashboard') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sla-dashboard*') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt w-4 text-center"></i> {{ __('sidebar.sla') }}
            </a>
            <a href="{{ route('sla-configs.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sla-configs*') || request()->is('sla-logs*') ? 'active' : '' }}">
                <i class="fas fa-stopwatch w-4 text-center"></i> {{ __('sidebar.sla_settings') }}
            </a>
        </div>

        {{-- ANALYTICS --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.analytics') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="{{ route('dashboard.stats') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('dashboard*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar w-4 text-center"></i> {{ __('sidebar.dashboard') }}
            </a>
            <a href="{{ route('sentiment.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sentiment*') ? 'active' : '' }}">
                <i class="fas fa-smile w-4 text-center"></i> {{ __('sidebar.sentiment') }}
            </a>
            <a href="{{ route('ratings.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('ratings*') ? 'active' : '' }}">
                <i class="fas fa-star w-4 text-center"></i> {{ __('sidebar.ratings') }}
            </a>
            <a href="{{ route('logger.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('logger*') ? 'active' : '' }}">
                <i class="fas fa-history w-4 text-center"></i> {{ __('sidebar.activity_logs') }}
            </a>
        </div>

        {{-- INTEGRATIONS --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.integrations') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 300px;' : 'max-height: 0;'">
            <a href="{{ route('store.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('store*') ? 'active' : '' }}">
                <i class="fas fa-store w-4 text-center"></i> {{ __('sidebar.e_commerce') }}
            </a>
            <a href="{{ route('sheets.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('sheets*') ? 'active' : '' }}">
                <i class="fas fa-table w-4 text-center"></i> {{ __('sidebar.google_sheets') }}
            </a>
        </div>

        {{-- SETTINGS --}}
        <div class="nav-group-header flex items-center justify-between px-3 py-2 mt-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500" x-data="{ open: false }" @click="open = !open; $el.classList.toggle('open')">
            <span>{{ __('sidebar.settings') }}</span>
            <i class="fas fa-chevron-right text-[9px] chevron"></i>
        </div>
        <div class="nav-group-body space-y-0.5" style="max-height: 0;" x-bind:style="open ? 'max-height: 500px;' : 'max-height: 0;'">
            <a href="{{ route('servers.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('servers*') ? 'active' : '' }}">
                <i class="fas fa-server w-4 text-center"></i> {{ __('sidebar.server') }}
            </a>
            <a href="{{ route('subscriptions.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('subscriptions*') ? 'active' : '' }}">
                <i class="fas fa-id-card w-4 text-center"></i> {{ __('sidebar.subscription') }}
            </a>
            <a href="{{ route('plans.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('plans*') && !request()->is('admin*') ? 'active' : '' }}">
                <i class="fas fa-box w-4 text-center"></i> {{ __('sidebar.plans') }}
            </a>
            <a href="{{ route('tokens.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('tokens*') ? 'active' : '' }}">
                <i class="fas fa-key w-4 text-center"></i> {{ __('sidebar.api_tokens') }}
            </a>
            <a href="{{ route('payouts.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('payouts*') && !request()->is('admin*') ? 'active' : '' }}">
                <i class="fas fa-wallet w-4 text-center"></i> {{ __('sidebar.payout') }}
            </a>
            @if(Auth::user()->isAdmin())
            <div class="mt-2 pt-2 border-t border-white/10">
                <div class="px-3 py-1 text-[10px] font-semibold text-gray-600 uppercase tracking-wider">{{ __('sidebar.admin') }}</div>
            </div>
            <a href="{{ route('admin.users.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="fas fa-users-cog w-4 text-center"></i> {{ __('sidebar.users') }}
            </a>
            <a href="{{ route('admin.plans.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/plans*') ? 'active' : '' }}">
                <i class="fas fa-box w-4 text-center"></i> {{ __('sidebar.plans') }}
            </a>
            <a href="{{ route('admin.vouchers.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/vouchers*') ? 'active' : '' }}">
                <i class="fas fa-ticket-alt w-4 text-center"></i> {{ __('sidebar.vouchers') }}
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/transactions*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt w-4 text-center"></i> {{ __('sidebar.transactions') }}
            </a>
            <a href="{{ route('admin.shorteners.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/shorteners*') ? 'active' : '' }}">
                <i class="fas fa-link w-4 text-center"></i> {{ __('sidebar.url_shortener') }}
            </a>
            <a href="{{ route('admin.pages.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/pages*') ? 'active' : '' }}">
                <i class="fas fa-file-alt w-4 text-center"></i> {{ __('sidebar.cms') }}
            </a>
            <a href="{{ route('admin.blog.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/blog*') ? 'active' : '' }}">
                <i class="fas fa-blog w-4 text-center"></i> {{ __('sidebar.blog') }}
            </a>
            <a href="{{ route('admin.payouts.index') }}" class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-300 {{ request()->is('admin/payouts*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd w-4 text-center"></i> {{ __('sidebar.payout_approval') }}
            </a>
            @endif
        </div>
    </nav>

    <div class="p-3 border-t border-white/10 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-brand-500 flex items-center justify-center text-white text-xs font-bold">
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</div>
            <div class="text-[11px] text-gray-500">{{ __('sidebar.online') }}</div>
        </div>
        <div class="flex items-center gap-1">
            @include('components.language-switcher', [
                'languages' => $langs ?? \App\Models\Language::active()->ordered()->get(),
                'currentLocale' => $cur ?? app()->getLocale(),
            ])
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="text-gray-500 hover:text-red-400 transition"><i class="fas fa-sign-out-alt text-sm"></i></button>
        </form>
    </div>
</aside>

{{-- Main Content --}}
<div class="flex-1 flex flex-col min-h-screen min-w-0">
    {{-- Topbar --}}
    <header class="topbar sticky top-0 z-30 border-b border-gray-200/60">
        <div class="flex items-center justify-between px-5 h-14">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition">
                <i class="fas fa-bars text-gray-600 text-lg"></i>
            </button>
            <div class="flex items-center gap-4 ml-auto">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-50 border border-green-200">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-xs font-medium text-green-700">{{ \App\Models\WaSession::where('user_id', Auth::id())->where('status','connected')->count() }} {{ __('sidebar.agents_online') }}</span>
                </div>
            </div>
        </div>
    </header>

    {{-- Content --}}
    <main class="flex-1 p-5 lg:p-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm font-medium flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-amber-500"></i> {{ session('warning') }}
            </div>
        @endif
        @yield('content')
    </main>
</div>
</div>
@endauth

@guest
<main class="min-h-screen flex items-center justify-center p-4">
    @yield('content')
</main>
@endguest

@stack('scripts')
</body>
</html>
