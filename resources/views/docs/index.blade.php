<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <meta property="og:title" content="{{ $seoMeta['title'] }}">
    <meta property="og:description" content="{{ $seoMeta['description'] }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seoMeta['canonical'] }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        html { scroll-behavior: smooth; }
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity .7s, transform .7s cubic-bezier(.16,1,.3,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": {{ json_encode($seoMeta['title']) }},
        "description": {{ json_encode($seoMeta['description']) }},
        "author": { "@type": "Organization", "name": "WABot" },
        "url": {{ json_encode($seoMeta['canonical']) }}
    }
    </script>
</head>
<body class="bg-white text-gray-900">

@php $lang = app()->getLocale(); @endphp

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-5 h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-extrabold tracking-tight"><i class="fas fa-paper-plane text-brand-500"></i> WABot</a>
        <div class="flex items-center gap-4">
            <a href="/docs" class="text-sm text-brand-600 font-semibold">{{ __('nav.docs') }}</a>
            <a href="/blog" class="text-sm text-gray-600 hover:text-brand-600 font-medium">{{ __('nav.blog') }}</a>
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-brand-600 font-medium">{{ __('nav.login') }}</a>
            <a href="{{ route('register') }}" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">{{ __('nav.register') }}</a>
        </div>
    </div>
</nav>

{{-- Jump Nav --}}
<div class="sticky top-16 z-40 bg-white border-b border-gray-100 overflow-x-auto" x-data>
    <div class="max-w-6xl mx-auto flex items-center gap-1 px-5 py-2 text-sm">
        <a href="#demo" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium">@if($lang === 'id')Akun Demo@elseDemo Accounts@endif</a>
        <a href="#menu" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium">@if($lang === 'id')Struktur Menu@elseMenu Structure@endif</a>
        <a href="#tutorial" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium">@if($lang === 'id')Tutorial@elseTutorial@endif</a>
        <a href="#fitur" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium">@if($lang === 'id')Fitur@elseFeatures@endif</a>
        <a href="#cta" class="px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 whitespace-nowrap font-medium">@if($lang === 'id')Mulai@elseGet Started@endif</a>
    </div>
</div>

{{-- Hero --}}
<section class="bg-gradient-to-r from-brand-600 to-brand-800 py-16 lg:py-20">
    <div class="max-w-6xl mx-auto px-5 text-white text-center">
        @if($lang === 'id')
            <h1 class="text-3xl lg:text-4xl font-extrabold mb-3">Dokumentasi WABot</h1>
            <p class="text-brand-200 text-lg max-w-2xl mx-auto">Platform WhatsApp Marketing SaaS terlengkap. Kelola chat, broadcast, campaign, AI agent, multi-channel, CRM, e-commerce, social media, dan analytics — semua dalam satu dashboard.</p>
        @else
            <h1 class="text-3xl lg:text-4xl font-extrabold mb-3">WABot Documentation</h1>
            <p class="text-brand-200 text-lg max-w-2xl mx-auto">The complete WhatsApp Marketing SaaS platform. Manage chats, broadcasts, campaigns, AI agents, multi-channel, CRM, e-commerce, social media, and analytics — all in one dashboard.</p>
        @endif
    </div>
</section>

{{-- Demo Accounts --}}
<section id="demo" class="max-w-6xl mx-auto px-5 py-16 reveal">
    <h2 class="text-2xl font-extrabold mb-2 text-center">@if($lang === 'id')Akun Demo@elseDemo Accounts@endif</h2>
    <p class="text-gray-500 text-sm text-center mb-6 max-w-lg mx-auto">
        @if($lang === 'id')
            Gunakan akun berikut untuk menjelajahi dashboard WABot. Admin memiliki akses penuh, User untuk penggunaan standar.
        @else
            Use the following accounts to explore the WABot dashboard. Admin has full access, User for standard usage.
        @endif
    </p>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">@if($lang === 'id')Peran@elseRole@endif</th>
                    <th class="px-5 py-3">Email</th>
                    <th class="px-5 py-3">@if($lang === 'id')Kata Sandi@elsePassword@endif</th>
                    <th class="px-5 py-3">@if($lang === 'id')Cakupan@elseScope@endif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3 font-semibold text-gray-900">@if($lang === 'id')Administrator@elseAdmin@endif</td>
                    <td class="px-5 py-3 font-mono text-sm text-brand-600">admin@wabot.test</td>
                    <td class="px-5 py-3 font-mono text-sm text-gray-600">password</td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        @if($lang === 'id')
                            Akses penuh — server, pengguna, voucher, transaksi, payout, CMS, blog
                        @else
                            Full access — servers, users, vouchers, transactions, payouts, CMS, blog
                        @endif
                    </td>
                </tr>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-5 py-3 font-semibold text-gray-900">@if($lang === 'id')Pengguna@elseUser@endif</td>
                    <td class="px-5 py-3 font-mono text-sm text-brand-600">user@wabot.test</td>
                    <td class="px-5 py-3 font-mono text-sm text-gray-600">password</td>
                    <td class="px-5 py-3 text-sm text-gray-500">
                        @if($lang === 'id')
                            Standar — chat, kontak, campaign, auto-reply, sessions
                        @else
                            Standard — chat, contacts, campaigns, auto-reply, sessions
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-brand-600 hover:underline text-sm font-semibold">
            <i class="fas fa-sign-in-alt"></i>
            @if($lang === 'id')Masuk ke Demo@elseLogin to Demo@endif
        </a>
    </div>
</section>

{{-- Menu Structure --}}
<section id="menu" class="bg-gray-50 py-16">
    <div class="max-w-6xl mx-auto px-5">
        <h2 class="text-2xl font-extrabold mb-2 text-center">@if($lang === 'id')Struktur Menu Admin@elseAdmin Menu Structure@endif</h2>
        <p class="text-gray-500 text-sm text-center mb-8 max-w-lg mx-auto">
            @if($lang === 'id')
                Semua grup navigasi dan item menu yang tersedia di dashboard WABot.
            @else
                All navigation groups and menu items available in the WABot dashboard.
            @endif
        </p>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Overview --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-chart-pie text-blue-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Ikhtisar@elseOverview@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm">
                        <i class="fas fa-th-large text-gray-400 mt-0.5 text-xs w-4 text-center"></i>
                        <div><span class="font-medium text-gray-800">@if($lang === 'id')Dashboard@elseDashboard@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Ringkasan metrik & statistik@elseMetrics & statistics overview@endif</p></div>
                    </li>
                    <li class="flex items-start gap-2 text-sm">
                        <i class="fas fa-stream text-gray-400 mt-0.5 text-xs w-4 text-center"></i>
                        <div><span class="font-medium text-gray-800">@if($lang === 'id')Aktivitas@elseActivity Feed@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Log aktivitas real-time@elseReal-time activity log@endif</p></div>
                    </li>
                </ul>
            </div>

            {{-- Inbox --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-inbox text-green-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Kotak Masuk@elseInbox@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-comments text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Live Chat@elseLive Chat@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Chat real-time pelanggan@elseReal-time customer chat@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-comment-dots text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Percakapan@elseConversations@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Semua percakapan@elseAll conversations@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-layer-group text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Antrian@elseQueue@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Antrian pesan masuk@elseIncoming message queue@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-paper-plane text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Pesan Terkirim@elseSent Messages@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Riwayat pesan keluar@elseOutgoing message history@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-address-book text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Kontak@elseContacts@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Manajemen kontak@elseContact management@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-users text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Grup@elseGroups@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Grup WhatsApp & label@elseWhatsApp groups & labels@endif</p></div></li>
                </ul>
            </div>

            {{-- WhatsApp --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                        <i class="fab fa-whatsapp text-emerald-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">WhatsApp</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-server text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Sesi / Agen@elseSessions / Agents@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola sesi Baileys & QR@elseManage Baileys sessions & QR@endif</p></div></li>
                </ul>
            </div>

            {{-- Marketing --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center">
                        <i class="fas fa-bullhorn text-orange-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Marketing@elseMarketing@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-broadcast-tower text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Broadcast@elseBroadcast@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kirim pesan massal@elseSend bulk messages@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-calendar-check text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Campaigns@elseCampaigns@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola kampanye marketing@elseManage marketing campaigns@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-clock text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Terjadwal@elseScheduled@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Pesan terjadwal@elseScheduled messages@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-tint text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Drip@elseDrip@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Urutan pesan bertahap@elseDrip message sequences@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-flask text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')A/B Testing@elseA/B Testing@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Uji varian pesan@elseTest message variants@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-chart-bar text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Click Tracking@elseClick Tracking@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Lacak klik tautan@elseTrack link clicks@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-file-alt text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Template@elseTemplates@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Template pesan siap pakai@elseReady-to-use message templates@endif</p></div></li>
                </ul>
            </div>

            {{-- AI Automation --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                        <i class="fas fa-robot text-violet-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')AI Automation@elseAI Automation@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-brain text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')AI Agents@elseAI Agents@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Agen AI untuk chat otomatis@elseAI agents for auto chat@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-bullseye text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Intent@elseIntent@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Deteksi maksud pesan@elseMessage intent detection@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-reply-all text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Auto Reply@elseAuto Reply@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Balas otomatis kata kunci@elseKeyword auto-reply@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-book text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Knowledge Base@elseKnowledge Base@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Basis pengetahuan AI@elseAI knowledge base@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-key text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')AI API Keys@elseAI API Keys@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola kunci API AI@elseManage AI API keys@endif</p></div></li>
                </ul>
            </div>

            {{-- Commerce --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-pink-100 flex items-center justify-center">
                        <i class="fas fa-store text-pink-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Commerce@elseCommerce@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-boxes text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Katalog@elseCatalog@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Katalog produk WhatsApp@elseWhatsApp product catalog@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-shopping-cart text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Pesanan@elseOrders@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id'))Kelola pesanan via WA@elseManage WA orders@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-hand-pointer text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Buttons@elseButtons@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Tombol interaktif WA@elseInteractive WA buttons@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-wpforms text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Forms@elseForms@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Formulir dalam chat@elseIn-chat forms@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-image text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Media@elseMedia@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Galeri media produk@elseProduct media gallery@endif</p></div></li>
                </ul>
            </div>

            {{-- CRM --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                        <i class="fas fa-user-tie text-teal-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">CRM</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-funnel-dollar text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Pipeline@elsePipeline@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id'))Pipeline penjualan@elseSales pipeline@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-handshake text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Deals@elseDeals@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola deal penjualan@elseManage sales deals@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-columns text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Kanban@elseKanban@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id'))Tampilan papan Kanban@elseKanban board view@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-tags text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Tags@elseTags@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Label & segmentasi kontak@elseContact labels & segmentation@endif</p></div></li>
                </ul>
            </div>

            {{-- Channels --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal md:col-span-2 lg:col-span-3">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-satellite-dish text-indigo-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id'))Channels@elseChannels@endif</h3>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2">
                    @php
                    $channels = [
                        ['icon' => 'fab fa-whatsapp', 'label_id' => 'WhatsApp Cloud', 'label_en' => 'WhatsApp Cloud'],
                        ['icon' => 'fas fa-phone', 'label_id' => 'WhatsApp Calling', 'label_en' => 'WhatsApp Calling'],
                        ['icon' => 'fab fa-instagram', 'label_id' => 'Instagram', 'label_en' => 'Instagram'],
                        ['icon' => 'fas fa-globe', 'label_id' => 'Webhooks', 'label_en' => 'Webhooks'],
                        ['icon' => 'fab fa-telegram', 'label_id' => 'Telegram', 'label_en' => 'Telegram'],
                        ['icon' => 'fab fa-facebook', 'label_id' => 'Facebook', 'label_en' => 'Facebook'],
                        ['icon' => 'fas fa-comment', 'label_id' => 'GBM', 'label_en' => 'GBM'],
                        ['icon' => 'fab fa-discord', 'label_id' => 'Discord', 'label_en' => 'Discord'],
                        ['icon' => 'fas fa-sms', 'label_id' => 'SMS', 'label_en' => 'SMS'],
                        ['icon' => 'fas fa-envelope', 'label_id' => 'Email', 'label_en' => 'Email'],
                        ['icon' => 'fab fa-tiktok', 'label_id' => 'TikTok', 'label_en' => 'TikTok'],
                        ['icon' => 'fab fa-line', 'label_id' => 'LINE', 'label_en' => 'LINE'],
                        ['icon' => 'fab fa-x-twitter', 'label_id' => 'X/Twitter', 'label_en' => 'X/Twitter'],
                    ];
                    @endphp
                    @foreach($channels as $ch)
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-3 py-2 text-sm">
                        <i class="{{ $ch['icon'] }} text-gray-500 text-xs w-4 text-center"></i>
                        <span class="font-medium text-gray-700">{{ $lang === 'id' ? $ch['label_id'] : $ch['label_en'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Team --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-cyan-100 flex items-center justify-center">
                        <i class="fas fa-users-cog text-cyan-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Tim@elseTeam@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-share-alt text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Shared Inbox@elseShared Inbox@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kotak masuk bersama tim@elseTeam shared inbox@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-user-friends text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Anggota@elseMembers@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Anggota tim & role@elseTeam members & roles@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-stopwatch text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')SLA@elseSLA@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Service level agreement@elseService level agreement@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-cog text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Pengaturan SLA@elseSLA Settings@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Konfigurasi SLA@elseSLA configuration@endif</p></div></li>
                </ul>
            </div>

            {{-- Analytics --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center">
                        <i class="fas fa-chart-line text-rose-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Analytics@elseAnalytics@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-tachometer-alt text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Dashboard Analytics@elseAnalytics Dashboard@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Metrik performa lengkap@elseFull performance metrics@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-smile text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Sentiment@elseSentiment@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Analisis sentimen chat@elseChat sentiment analysis@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-star text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Ratings@elseRatings@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Rating & feedback pelanggan@elseCustomer ratings & feedback@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-history text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Log Aktivitas@elseActivity Logs@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Riwayat semua aktivitas@elseFull activity history@endif</p></div></li>
                </ul>
            </div>

            {{-- Integrations --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                        <i class="fas fa-plug text-amber-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Integrasi@elseIntegrations@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-shopping-bag text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')E-Commerce@elseE-Commerce@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Integrasi WooCommerce dll@elseWooCommerce & more@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-table text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Google Sheets@elseGoogle Sheets@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Sinkronisasi spreadsheet@elseSpreadsheet sync@endif</p></div></li>
                </ul>
            </div>

            {{-- AI Studio --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-fuchsia-100 flex items-center justify-center">
                        <i class="fas fa-wand-magic-sparkles text-fuchsia-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')AI Studio@elseAI Studio@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-pen-to-square text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Content@elseContent@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Generate konten AI@elseAI content generation@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-image text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Image@elseImage@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Generate gambar AI@elseAI image generation@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-calendar-day text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Planner@elsePlanner@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Perencana konten AI@elseAI content planner@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-hourglass-half text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Best Time@elseBest Time@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Waktu kirim optimal@elseOptimal send time@endif</p></div></li>
                </ul>
            </div>

            {{-- Social Media --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center">
                        <i class="fas fa-hashtag text-sky-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Social Media@elseSocial Media@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-pencil text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Composer@elseComposer@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Buat postingan sosial@elseCreate social posts@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-calendar text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Calendar@elseCalendar@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kalender konten@elseContent calendar@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-list-ul text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Queue@elseQueue@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Antrian posting@elsePost queue@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-file-lines text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')Drafts@elseDrafts@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Draf postingan@elsePost drafts@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-rss text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id')RSS@elseRSS@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Auto-post dari RSS feed@elseAuto-post from RSS feeds@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-closed-captioning text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Captions@elseCaptions@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Generate caption AI@elseAI caption generation@endif</p></div></li>
                </ul>
            </div>

            {{-- Settings --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-sliders-h text-gray-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Pengaturan@elseSettings@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-server text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Server@elseServer@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Konfigurasi server@elseServer configuration@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-crown text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Subscription@elseSubscription@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola langganan@elseManage subscription@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-list-check text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Plans@elsePlans@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Paket harga & upgrade@elsePricing plans & upgrade@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-code text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))API Tokens@elseAPI Tokens@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Token API developer@elseDeveloper API tokens@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-money-bill-wave text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Payout@elsePayout@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Penarikan dana@elseFund withdrawal@endif</p></div></li>
                </ul>
            </div>

            {{-- Admin --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center">
                        <i class="fas fa-shield-haltered text-red-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Admin@elseAdmin@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-users text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Users@elseUsers@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Manajemen pengguna@elseUser management@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-list-check text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Plans@elsePlans@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola paket & harga@elseManage plans & pricing@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-ticket text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Vouchers@elseVouchers@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kupon diskon@elseDiscount coupons@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-receipt text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Transactions@elseTransactions@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Riwayat transaksi@elseTransaction history@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-link text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Shortener@elseShortener@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Short link manager@elseShort link manager@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-newspaper text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">CMS</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Manajemen konten@elseContent management@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-blog text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Blog@elseBlog@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kelola artikel blog@elseManage blog articles@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-check-double text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Payout Approval@elsePayout Approval@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Setujui penarikan@elseApprove withdrawals@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-coins text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Credit Packs@elseCredit Packs@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Paket kredit@elseCredit packages@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-percent text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Coupons@elseCoupons@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Kupon admin@elseAdmin coupons@endif</p></div></li>
                </ul>
            </div>

            {{-- Earn --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 card-lift reveal">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-sack-dollar text-yellow-600 text-sm"></i>
                    </div>
                    <h3 class="font-bold text-gray-900">@if($lang === 'id')Dapatkan@elseEarn@endif</h3>
                </div>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-gem text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Credits@elseCredits@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Beli & kelola kredit@elseBuy & manage credits@endif</p></div></li>
                    <li class="flex items-start gap-2 text-sm"><i class="fas fa-hand-holding-heart text-gray-400 mt-0.5 text-xs w-4 text-center"></i><div><span class="font-medium text-gray-800">@if($lang === 'id'))Affiliate@elseAffiliate@endif</span><p class="text-xs text-gray-400 mt-0.5">@if($lang === 'id')Program afiliasi@elseAffiliate program@endif</p></div></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Tutorial --}}
<section id="tutorial" class="max-w-5xl mx-auto px-5 py-16">
    <h2 class="text-2xl font-extrabold mb-2 text-center">@if($lang === 'id')Tutorial Langkah demi Langkah@elseStep by Step Tutorial@endif</h2>
    <p class="text-gray-500 text-sm text-center mb-10 max-w-lg mx-auto">
        @if($lang === 'id')
            Ikuti panduan lengkap dari pendaftaran hingga menguasai seluruh fitur WABot.
        @else
            Follow the complete guide from registration to mastering all WABot features.
        @endif
    </p>

    @php
    $tutorials = [
        [
            'icon' => 'fas fa-user-plus',
            'phase_id' => 'Fase 1',
            'phase_en' => 'Phase 1',
            'title_id' => 'Pendaftaran & Login',
            'title_en' => 'Registration & Login',
            'steps_id' => [
                'Buka halaman pendaftaran WABot dan isi formulir dengan nama, email, dan kata sandi.',
                'Verifikasi email Anda melalui tautan yang dikirim ke inbox.',
                'Login ke dashboard menggunakan email dan kata sandi yang telah didaftarkan.',
                'Lengkapi profil bisnis Anda — nama bisnis, logo, dan preferensi bahasa.',
            ],
            'steps_en' => [
                'Open the WABot registration page and fill in the form with your name, email, and password.',
                'Verify your email via the link sent to your inbox.',
                'Log into the dashboard using your registered email and password.',
                'Complete your business profile — business name, logo, and language preferences.',
            ],
        ],
        [
            'icon' => 'fas fa-server',
            'phase_id' => 'Fase 2',
            'phase_en' => 'Phase 2',
            'title_id' => 'Setup Server (Baileys)',
            'title_en' => 'Server Setup (Baileys)',
            'steps_id' => [
                'Buka menu WhatsApp > Sessions/Agents.',
                'Klik "Add Server" untuk membuat server Baileys baru.',
                'Pilih plan server yang sesuai dengan kebutuhan Anda (free/paid).',
                'Konfigurasi nama server dan webhook URL jika diperlukan.',
                'Server akan otomatis ter-deploy dan siap digunakan.',
            ],
            'steps_en' => [
                'Open WhatsApp > Sessions/Agents menu.',
                'Click "Add Server" to create a new Baileys server.',
                'Choose a server plan that fits your needs (free/paid).',
                'Configure server name and webhook URL if needed.',
                'The server will auto-deploy and be ready for use.',
            ],
        ],
        [
            'icon' => 'fas fa-qrcode',
            'phase_id' => 'Fase 3',
            'phase_en' => 'Phase 3',
            'title_id' => 'Hubungkan WhatsApp (Scan QR)',
            'title_en' => 'Connect WhatsApp (Scan QR)',
            'steps_id' => [
                'Pada halaman Sessions, klik server yang baru dibuat.',
                'Klik tombol "Connect" untuk menampilkan QR code.',
                'Buka WhatsApp di ponsel Anda, masuk ke Settings > Linked Devices.',
                'Scan QR code yang muncul di layar dashboard WABot.',
                'Tunggu beberapa detik hingga status berubah menjadi "Connected".',
            ],
            'steps_en' => [
                'On the Sessions page, click the newly created server.',
                'Click the "Connect" button to display the QR code.',
                'Open WhatsApp on your phone, go to Settings > Linked Devices.',
                'Scan the QR code shown on the WABot dashboard.',
                'Wait a few seconds until the status changes to "Connected".',
            ],
        ],
        [
            'icon' => 'fas fa-address-book',
            'phase_id' => 'Fase 4',
            'phase_en' => 'Phase 4',
            'title_id' => 'Manajemen Kontak',
            'title_en' => 'Contact Management',
            'steps_id' => [
                'Buka menu Inbox > Contacts untuk melihat semua kontak.',
                'Klik "Add Contact" untuk menambah kontak secara manual — isi nama, nomor WA, email, dan tag.',
                'Untuk import massal, klik "Import" dan unggah file CSV dengan format: nama, nomor, tag.',
                'Buat Groups untuk mengelompokkan kontak (misal: Pelanggan, Leads, VIP).',
                'Gunakan Tags untuk segmentasi dinamis berdasarkan kategori atau perilaku.',
                'Verifikasi data yang diimport — sistem akan mendeteksi nomor duplikat dan invalid.',
            ],
            'steps_en' => [
                'Open Inbox > Contacts to view all contacts.',
                'Click "Add Contact" to manually add a contact — fill in name, WA number, email, and tags.',
                'For bulk import, click "Import" and upload a CSV file with format: name, number, tags.',
                'Create Groups to organize contacts (e.g., Customers, Leads, VIP).',
                'Use Tags for dynamic segmentation based on categories or behavior.',
                'Verify imported data — the system detects duplicate and invalid numbers.',
            ],
        ],
        [
            'icon' => 'fas fa-reply-all',
            'phase_id' => 'Fase 5',
            'phase_en' => 'Phase 5',
            'title_id' => 'Setup Auto-Reply',
            'title_en' => 'Auto-Reply Setup',
            'steps_id' => [
                'Buka AI Automation > Auto Reply.',
                'Klik "New Rule" untuk membuat aturan auto-reply baru.',
                'Tentukan tipe: Keyword (balas saat ada kata kunci) / Welcome (balas kontak baru) / Fallback (balas saat tidak ada yang cocok).',
                'Masukkan kata kunci trigger (contoh: "harga", "promo", "alamat") dan pesan balasan.',
                'Atur prioritas rule — rule dengan prioritas lebih tinggi akan dieksekusi lebih dulu.',
                'Aktifkan rule dan tes dengan mengirim pesan sesuai trigger ke nomor WA Anda.',
            ],
            'steps_en' => [
                'Open AI Automation > Auto Reply.',
                'Click "New Rule" to create a new auto-reply rule.',
                'Choose type: Keyword (reply on keyword match) / Welcome (greet new contacts) / Fallback (reply when nothing matches).',
                'Enter trigger keywords (e.g., "price", "promo", "address") and reply message.',
                'Set rule priority — higher priority rules execute first.',
                'Activate the rule and test by sending a matching message to your WA number.',
            ],
        ],
        [
            'icon' => 'fas fa-bullhorn',
            'phase_id' => 'Fase 6',
            'phase_en' => 'Phase 6',
            'title_id' => 'Campaign & Broadcast',
            'title_en' => 'Campaigns & Broadcast',
            'steps_id' => [
                'Buka Marketing > Broadcast untuk mengirim pesan massal.',
                'Pilih penerima: semua kontak, grup tertentu, atau filter berdasarkan tag.',
                'Tulis pesan broadcast — gunakan variabel {{name}} untuk personalisasi otomatis.',
                'Jadwalkan pengiriman (sekarang atau nanti) dan atur interval delay antar pesan.',
                'Untuk campaign multi-langkah, buka Marketing > Campaigns dan buat urutan pesan drip.',
                'Aktifkan A/B Testing untuk menguji varian pesan dan lihat mana yang performa terbaik.',
                'Gunakan Click Tracking untuk melacak berapa banyak penerima yang mengklik tautan Anda.',
                'Pantau statistik pengiriman: delivered, read, replied di dashboard broadcast.',
            ],
            'steps_en' => [
                'Open Marketing > Broadcast to send bulk messages.',
                'Select recipients: all contacts, specific groups, or filter by tags.',
                'Compose your broadcast message — use {{name}} variables for auto-personalization.',
                'Schedule delivery (now or later) and set interval delay between messages.',
                'For multi-step campaigns, go to Marketing > Campaigns and create drip message sequences.',
                'Enable A/B Testing to test message variants and see which performs best.',
                'Use Click Tracking to monitor how many recipients clicked your links.',
                'Monitor delivery stats: delivered, read, replied on the broadcast dashboard.',
            ],
        ],
        [
            'icon' => 'fas fa-brain',
            'phase_id' => 'Fase 7',
            'phase_en' => 'Phase 7',
            'title_id' => 'AI Agents & Knowledge Base',
            'title_en' => 'AI Agents & Knowledge Base',
            'steps_id' => [
                'Buka AI Automation > AI Agents dan klik "Create Agent".',
                'Beri nama agent (contoh: "CS Bot"), pilih model AI, dan tulis system prompt (personality & instruksi).',
                'Buka AI Automation > Knowledge Base untuk menambah dokumen pengetahuan.',
                'Upload PDF, TXT, atau dokumen web — AI akan menggunakan ini sebagai referensi menjawab.',
                'Hubungkan Agent dengan Knowledge Base agar jawaban selalu kontekstual.',
                'Buka AI Automation > Intent untuk mendefinisikan maksud pesan (salam, komplain, tanya harga).',
                'Tes agent melalui simulasi chat di dashboard sebelum di-deploy ke production.',
            ],
            'steps_en' => [
                'Open AI Automation > AI Agents and click "Create Agent".',
                'Name your agent (e.g., "CS Bot"), choose an AI model, and write a system prompt (personality & instructions).',
                'Go to AI Automation > Knowledge Base to add knowledge documents.',
                'Upload PDF, TXT, or web documents — the AI will use these as reference for answers.',
                'Link Agent with Knowledge Base so answers are always contextual.',
                'Go to AI Automation > Intent to define message intents (greeting, complaint, price inquiry).',
                'Test the agent via chat simulation in the dashboard before deploying to production.',
            ],
        ],
        [
            'icon' => 'fas fa-project-diagram',
            'phase_id' => 'Fase 8',
            'phase_en' => 'Phase 8',
            'title_id' => 'Flow Builder (Chatbot Visual)',
            'title_en' => 'Flow Builder (Visual Chatbot)',
            'steps_id' => [
                'Buka menu AI Automation dan pilih Flow Builder.',
                'Drag & drop node untuk membangun alur percakapan visual — tanpa coding.',
                'Tambahkan node: Send Message, Wait for Reply, Condition (IF/ELSE), AI Response, API Call.',
                'Hubungkan antar node dengan connector untuk mendefinisikan alur percakapan.',
                'Tes flow dengan simulator interaktif — lihat preview percakapan langsung.',
                'Deploy flow ke nomor WhatsApp Anda — bot siap merespon otomatis.',
            ],
            'steps_en' => [
                'Open AI Automation menu and select Flow Builder.',
                'Drag & drop nodes to build visual conversation flows — no coding required.',
                'Add nodes: Send Message, Wait for Reply, Condition (IF/ELSE), AI Response, API Call.',
                'Connect nodes with connectors to define the conversation flow.',
                'Test the flow with interactive simulator — preview live conversation.',
                'Deploy the flow to your WhatsApp number — your bot is ready to auto-respond.',
            ],
        ],
        [
            'icon' => 'fas fa-satellite-dish',
            'phase_id' => 'Fase 9',
            'phase_en' => 'Phase 9',
            'title_id' => 'Multi-Channel Setup',
            'title_en' => 'Multi-Channel Setup',
            'steps_id' => [
                'Buka menu Channels untuk melihat semua channel yang tersedia.',
                'Untuk WhatsApp Cloud API: daftar Meta Business, buat App, dapatkan token, dan hubungkan.',
                'Untuk Instagram: hubungkan akun Instagram Business/Facebook Page melalui oAuth.',
                'Untuk Telegram: buat bot via @BotFather, masukkan token bot ke WABot.',
                'Untuk channel lain (Facebook, Discord, SMS, Email, TikTok, LINE, X): ikuti wizard koneksi masing-masing.',
                'Semua channel akan muncul di Inbox terpusat — satu dashboard untuk semua pesan.',
            ],
            'steps_en' => [
                'Open Channels menu to see all available channels.',
                'For WhatsApp Cloud API: register Meta Business, create App, get token, and connect.',
                'For Instagram: connect Instagram Business/Facebook Page account via oAuth.',
                'For Telegram: create bot via @BotFather, enter bot token into WABot.',
                'For other channels (Facebook, Discord, SMS, Email, TikTok, LINE, X): follow each connection wizard.',
                'All channels appear in the unified Inbox — one dashboard for all messages.',
            ],
        ],
        [
            'icon' => 'fas fa-users-cog',
            'phase_id' => 'Fase 10',
            'phase_en' => 'Phase 10',
            'title_id' => 'Team Inbox & SLA',
            'title_en' => 'Team Inbox & SLA',
            'steps_id' => [
                'Buka Team > Members untuk mengundang anggota tim via email.',
                'Tetapkan role untuk setiap anggota: Admin, Agent, Viewer.',
                'Buka Team > Shared Inbox — semua anggota tim dapat melihat dan merespon pesan yang sama.',
                'Fitur assignment: tugaskan percakapan ke anggota tim tertentu.',
                'Buka Team > SLA Settings untuk konfigurasi Service Level Agreement.',
                'Tentukan target response time (misal: 5 menit) dan resolution time (misal: 1 jam).',
                'Pantau performa tim di Team > SLA dashboard — response rate, resolution rate, avg time.',
            ],
            'steps_en' => [
                'Open Team > Members to invite team members via email.',
                'Assign roles: Admin, Agent, Viewer for each member.',
                'Open Team > Shared Inbox — all team members can view and respond to the same messages.',
                'Assignment feature: assign conversations to specific team members.',
                'Go to Team > SLA Settings to configure Service Level Agreement.',
                'Set target response time (e.g., 5 minutes) and resolution time (e.g., 1 hour).',
                'Monitor team performance at Team > SLA dashboard — response rate, resolution rate, avg time.',
            ],
        ],
        [
            'icon' => 'fas fa-shopping-cart',
            'phase_id' => 'Fase 11',
            'phase_en' => 'Phase 11',
            'title_id' => 'Integrasi E-Commerce',
            'title_en' => 'E-Commerce Integration',
            'steps_id' => [
                'Buka Integrations > E-Commerce.',
                'Pilih platform: WooCommerce (tersedia) — masukkan URL toko dan API key.',
                'Setelah terhubung, produk otomatis tersinkron ke Commerce > Catalog.',
                'Pelanggan bisa browse produk, tambah ke keranjang, dan checkout via chat WhatsApp.',
                'Buat tombol interaktif (Commerce > Buttons) untuk navigasi produk cepat.',
                'Pantau pesanan masuk di Commerce > Orders — status, pembayaran, pengiriman.',
            ],
            'steps_en' => [
                'Open Integrations > E-Commerce.',
                'Choose platform: WooCommerce (available) — enter store URL and API key.',
                'Once connected, products auto-sync to Commerce > Catalog.',
                'Customers can browse products, add to cart, and checkout via WhatsApp chat.',
                'Create interactive buttons (Commerce > Buttons) for quick product navigation.',
                'Monitor incoming orders at Commerce > Orders — status, payment, shipping.',
            ],
        ],
        [
            'icon' => 'fas fa-hashtag',
            'phase_id' => 'Fase 12',
            'phase_en' => 'Phase 12',
            'title_id' => 'Social Media Publishing',
            'title_en' => 'Social Media Publishing',
            'steps_id' => [
                'Buka Social Media > Composer untuk membuat postingan baru.',
                'Pilih channel target: Instagram, Facebook, Telegram, TikTok, X/Twitter.',
                'Tulis caption, tambahkan gambar/video, dan preview tampilan.',
                'Jadwalkan posting di Social Media > Calendar — pilih tanggal dan jam tayang.',
                'Gunakan Social Media > RSS untuk auto-post dari RSS feed ke social media.',
                'Generate caption AI di Social Media > Captions — pilih tone dan bahasa.',
                'Kelola draf di Social Media > Drafts sebelum publish.',
                'Pantau antrian posting di Social Media > Queue.',
            ],
            'steps_en' => [
                'Open Social Media > Composer to create new posts.',
                'Select target channels: Instagram, Facebook, Telegram, TikTok, X/Twitter.',
                'Write caption, add images/videos, and preview layout.',
                'Schedule posts at Social Media > Calendar — pick date and time.',
                'Use Social Media > RSS to auto-post from RSS feeds to social media.',
                'Generate AI captions at Social Media > Captions — choose tone and language.',
                'Manage drafts at Social Media > Drafts before publishing.',
                'Monitor post queue at Social Media > Queue.',
            ],
        ],
        [
            'icon' => 'fas fa-wand-magic-sparkles',
            'phase_id' => 'Fase 13',
            'phase_en' => 'Phase 13',
            'title_id' => 'AI Content Studio',
            'title_en' => 'AI Content Studio',
            'steps_id' => [
                'Buka AI Studio > Content untuk generate teks marketing, broadcast, caption.',
                'Pilih tipe konten, tone (formal, casual, friendly), panjang, dan bahasa.',
                'Buka AI Studio > Image untuk generate gambar promosi dan ilustrasi via AI.',
                'AI Studio > Planner: masukkan tujuan bisnis, AI akan buatkan rencana konten mingguan/bulanan.',
                'AI Studio > Best Time: analisis data chat untuk menentukan waktu kirim optimal ke pelanggan.',
                'Semua konten yang digenerate bisa langsung dijadwalkan atau disimpan ke draft.',
            ],
            'steps_en' => [
                'Open AI Studio > Content to generate marketing text, broadcasts, captions.',
                'Choose content type, tone (formal, casual, friendly), length, and language.',
                'Go to AI Studio > Image to generate promotional images and illustrations via AI.',
                'AI Studio > Planner: input business goals, AI creates weekly/monthly content plans.',
                'AI Studio > Best Time: analyze chat data to determine optimal send times to customers.',
                'All generated content can be directly scheduled or saved to drafts.',
            ],
        ],
        [
            'icon' => 'fas fa-chart-line',
            'phase_id' => 'Fase 14',
            'phase_en' => 'Phase 14',
            'title_id' => 'Analytics & Laporan',
            'title_en' => 'Analytics & Reports',
            'steps_id' => [
                'Buka Analytics > Dashboard untuk melihat metrik utama: total chat, response rate, resolution rate.',
                'Analytics > Sentiment: lihat tren sentimen pelanggan — positif, netral, negatif.',
                'Analytics > Ratings: pantau rating dan feedback dari pelanggan setelah interaksi.',
                'Analytics > Activity Logs: lihat log lengkap semua aktivitas sistem.',
                'Gunakan filter tanggal dan channel untuk analisis mendalam.',
                'Export data ke CSV/Excel untuk pelaporan eksternal.',
            ],
            'steps_en' => [
                'Open Analytics > Dashboard to see key metrics: total chats, response rate, resolution rate.',
                'Analytics > Sentiment: view customer sentiment trends — positive, neutral, negative.',
                'Analytics > Ratings: monitor ratings and feedback from customers after interactions.',
                'Analytics > Activity Logs: view complete logs of all system activities.',
                'Use date and channel filters for in-depth analysis.',
                'Export data to CSV/Excel for external reporting.',
            ],
        ],
        [
            'icon' => 'fas fa-coins',
            'phase_id' => 'Fase 15',
            'phase_en' => 'Phase 15',
            'title_id' => 'Monetisasi (Kredit, Afiliasi, Kupon)',
            'title_en' => 'Monetization (Credits, Affiliate, Coupons)',
            'steps_id' => [
                'Buka Earn > Credits untuk membeli paket kredit — kredit digunakan untuk AI, broadcast, API calls.',
                'Pilih paket yang sesuai dan selesaikan pembayaran.',
                'Earn > Affiliate: dapatkan link afiliasi unik dan bagikan ke jaringan Anda.',
                'Pantau komisi afiliasi, klik, konversi, dan saldo di dashboard afiliasi.',
                'Admin dapat mengelola Credit Packs, Coupons, dan Vouchers di menu Admin.',
                'Request payout melalui Settings > Payout — admin akan memproses penarikan.',
            ],
            'steps_en' => [
                'Open Earn > Credits to buy credit packs — credits are used for AI, broadcasts, API calls.',
                'Choose a package and complete payment.',
                'Earn > Affiliate: get your unique affiliate link and share with your network.',
                'Monitor affiliate commissions, clicks, conversions, and balance on the affiliate dashboard.',
                'Admins can manage Credit Packs, Coupons, and Vouchers in the Admin menu.',
                'Request payout via Settings > Payout — admin will process the withdrawal.',
            ],
        ],
    ];
    @endphp

    <div class="space-y-6">
        @foreach($tutorials as $i => $phase)
        <div class="reveal bg-white border border-gray-200 rounded-2xl p-6 card-lift">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-brand-600 flex items-center justify-center">
                    <i class="{{ $phase['icon'] }} text-white text-sm"></i>
                </div>
                <div>
                    <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">{{ $lang === 'id' ? $phase['phase_id'] : $phase['phase_en'] }}</span>
                    <h3 class="text-lg font-bold text-gray-900">{{ $lang === 'id' ? $phase['title_id'] : $phase['title_en'] }}</h3>
                </div>
            </div>
            <ol class="space-y-2">
                @php $steps = $lang === 'id' ? $phase['steps_id'] : $phase['steps_en']; @endphp
                @foreach($steps as $j => $step)
                <li class="flex items-start gap-3 text-sm text-gray-600">
                    <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">{{ $j + 1 }}</span>
                    <span>{{ $step }}</span>
                </li>
                @endforeach
            </ol>
        </div>
        @endforeach
    </div>
</section>

{{-- Feature Showcase --}}
<section id="fitur" class="bg-gray-50 py-16">
    <div class="max-w-6xl mx-auto px-5">
        <h2 class="text-2xl font-extrabold mb-2 text-center">@if($lang === 'id')Fitur Unggulan@elseFeature Showcase@endif</h2>
        <p class="text-gray-500 text-sm text-center mb-10 max-w-lg mx-auto">
            @if($lang === 'id')
                Semua fitur yang membuat WABot platform WhatsApp Marketing terlengkap.
            @else
                All features that make WABot the most complete WhatsApp Marketing platform.
            @endif
        </p>

        @php
        $featureGroups = [
            [
                'icon' => 'fas fa-comments',
                'group_id' => 'Omnichannel Inbox',
                'group_en' => 'Omnichannel Inbox',
                'items' => [
                    ['title_id' => 'Live Chat Real-Time', 'title_en' => 'Real-Time Live Chat', 'desc_id' => 'Pantau dan balas semua pesan dari berbagai channel dalam satu dashboard.', 'desc_en' => 'Monitor and reply to all messages from multiple channels in one dashboard.', 'bullets_id' => ['Multi-window chat', 'Typing indicator', 'File & media sharing', 'Emoji & quick replies'], 'bullets_en' => ['Multi-window chat', 'Typing indicator', 'File & media sharing', 'Emoji & quick replies']],
                    ['title_id' => 'Shared Team Inbox', 'title_en' => 'Shared Team Inbox', 'desc_id' => 'Beberapa agen dapat mengakses dan merespon pesan yang sama tanpa konflik.', 'desc_en' => 'Multiple agents can access and respond to the same messages without conflict.', 'bullets_id' => ['Agent assignment', 'Internal notes', 'Collision detection', 'Transfer chat antar agen'], 'bullets_en' => ['Agent assignment', 'Internal notes', 'Collision detection', 'Chat transfer between agents']],
                    ['title_id' => 'Contact & Group Management', 'title_en' => 'Contact & Group Management', 'desc_id' => 'Kelola ribuan kontak dengan tagging, segmentasi, dan grouping otomatis.', 'desc_en' => 'Manage thousands of contacts with automatic tagging, segmentation, and grouping.', 'bullets_id' => ['CSV import/export', 'Custom fields', 'Dynamic segments', 'Duplicate detection'], 'bullets_en' => ['CSV import/export', 'Custom fields', 'Dynamic segments', 'Duplicate detection']],
                ],
            ],
            [
                'icon' => 'fas fa-robot',
                'group_id' => 'AI Automation',
                'group_en' => 'AI Automation',
                'items' => [
                    ['title_id' => 'AI Chat Agents', 'title_en' => 'AI Chat Agents', 'desc_id' => 'Deploy agen AI yang bisa menjawab pertanyaan pelanggan 24/7 dengan natural language.', 'desc_en' => 'Deploy AI agents that answer customer questions 24/7 with natural language.', 'bullets_id' => ['Multiple AI models', 'Custom system prompt', 'Context memory', 'Fallback to human'], 'bullets_en' => ['Multiple AI models', 'Custom system prompt', 'Context memory', 'Fallback to human']],
                    ['title_id' => 'Visual Flow Builder', 'title_en' => 'Visual Flow Builder', 'desc_id' => 'Bangun chatbot canggih dengan drag & drop — tanpa coding.', 'desc_en' => 'Build advanced chatbots with drag & drop — no coding.', 'bullets_id' => ['Node-based editor', 'Conditional logic', 'API integration', 'Flow simulator'], 'bullets_en' => ['Node-based editor', 'Conditional logic', 'API integration', 'Flow simulator']],
                    ['title_id' => 'Knowledge Base', 'title_en' => 'Knowledge Base', 'desc_id' => 'Upload dokumen sebagai sumber pengetahuan AI untuk jawaban akurat dan kontekstual.', 'desc_en' => 'Upload documents as AI knowledge sources for accurate, contextual answers.', 'bullets_id' => ['PDF, TXT, Web support', 'Auto-vectorization', 'Semantic search', 'Multi-language'], 'bullets_en' => ['PDF, TXT, Web support', 'Auto-vectorization', 'Semantic search', 'Multi-language']],
                ],
            ],
            [
                'icon' => 'fas fa-bullhorn',
                'group_id' => 'Marketing & Campaign',
                'group_en' => 'Marketing & Campaign',
                'items' => [
                    ['title_id' => 'Broadcast Massal', 'title_en' => 'Bulk Broadcast', 'desc_id' => 'Kirim pesan ke ribuan kontak sekaligus dengan personalisasi dan scheduling.', 'desc_en' => 'Send messages to thousands of contacts at once with personalization and scheduling.', 'bullets_id' => ['{{name}} variables', 'Delay interval', 'Schedule send', 'Delivery reports'], 'bullets_en' => ['{{name}} variables', 'Delay interval', 'Schedule send', 'Delivery reports']],
                    ['title_id' => 'Drip Campaign', 'title_en' => 'Drip Campaign', 'desc_id' => 'Urutan pesan otomatis berdasarkan trigger waktu atau aksi pelanggan.', 'desc_en' => 'Automated message sequences triggered by time or customer actions.', 'bullets_id' => ['Multi-step flows', 'Time-based triggers', 'Action triggers', 'Conversion tracking'], 'bullets_en' => ['Multi-step flows', 'Time-based triggers', 'Action triggers', 'Conversion tracking']],
                    ['title_id' => 'A/B Testing', 'title_en' => 'A/B Testing', 'desc_id' => 'Uji varian pesan untuk menemukan copy dengan performa terbaik.', 'desc_en' => 'Test message variants to find the best-performing copy.', 'bullets_id' => ['Variant comparison', 'Statistical significance', 'Auto-winner selection', 'Open & click rates'], 'bullets_en' => ['Variant comparison', 'Statistical significance', 'Auto-winner selection', 'Open & click rates']],
                    ['title_id' => 'Message Templates', 'title_en' => 'Message Templates', 'desc_id' => 'Template pesan siap pakai untuk berbagai skenario bisnis.', 'desc_en' => 'Ready-to-use message templates for various business scenarios.', 'bullets_id' => ['Template library', 'Custom branding', 'Variable slots', 'Category tags'], 'bullets_en' => ['Template library', 'Custom branding', 'Variable slots', 'Category tags']],
                ],
            ],
            [
                'icon' => 'fas fa-chart-bar',
                'group_id' => 'Analytics & Insights',
                'group_en' => 'Analytics & Insights',
                'items' => [
                    ['title_id' => 'Dashboard Metrik', 'title_en' => 'Metrics Dashboard', 'desc_id' => 'Visualisasi lengkap performa chat, campaign, dan tim Anda.', 'desc_en' => 'Complete visualization of chat, campaign, and team performance.', 'bullets_id' => ['Real-time charts', 'Custom date ranges', 'Channel breakdown', 'Export reports'], 'bullets_en' => ['Real-time charts', 'Custom date ranges', 'Channel breakdown', 'Export reports']],
                    ['title_id' => 'Sentiment Analysis', 'title_en' => 'Sentiment Analysis', 'desc_id' => 'Deteksi otomatis sentimen pelanggan — positif, netral, atau negatif.', 'desc_en' => 'Automatic customer sentiment detection — positive, neutral, or negative.', 'bullets_id' => ['AI-powered analysis', 'Trend tracking', 'Alert on negative', 'Per-agent metrics'], 'bullets_en' => ['AI-powered analysis', 'Trend tracking', 'Alert on negative', 'Per-agent metrics']],
                ],
            ],
            [
                'icon' => 'fas fa-satellite-dish',
                'group_id' => 'Multi-Channel',
                'group_en' => 'Multi-Channel',
                'items' => [
                    ['title_id' => '13+ Channel Terintegrasi', 'title_en' => '13+ Integrated Channels', 'desc_id' => 'Hubungkan WhatsApp, Instagram, Telegram, Facebook, Discord, dan 8 channel lainnya.', 'desc_en' => 'Connect WhatsApp, Instagram, Telegram, Facebook, Discord, and 8 other channels.', 'bullets_id' => ['WhatsApp (Baileys + Cloud)', 'Instagram DM', 'Telegram Bot', 'Facebook Messenger', 'SMS, Email, TikTok, LINE, X'], 'bullets_en' => ['WhatsApp (Baileys + Cloud)', 'Instagram DM', 'Telegram Bot', 'Facebook Messenger', 'SMS, Email, TikTok, LINE, X']],
                ],
            ],
            [
                'icon' => 'fas fa-store',
                'group_id' => 'E-Commerce',
                'group_en' => 'E-Commerce',
                'items' => [
                    ['title_id' => 'Katalog & Pesanan WhatsApp', 'title_en' => 'WhatsApp Catalog & Orders', 'desc_id' => 'Jual produk langsung via WhatsApp dengan katalog interaktif dan tombol CTA.', 'desc_en' => 'Sell products directly via WhatsApp with interactive catalog and CTA buttons.', 'bullets_id' => ['Product catalog sync', 'Cart & checkout', 'Payment integration', 'Order notifications'], 'bullets_en' => ['Product catalog sync', 'Cart & checkout', 'Payment integration', 'Order notifications']],
                ],
            ],
            [
                'icon' => 'fas fa-hashtag',
                'group_id' => 'Social Media Manager',
                'group_en' => 'Social Media Manager',
                'items' => [
                    ['title_id' => 'Social Publishing Suite', 'title_en' => 'Social Publishing Suite', 'desc_id' => 'Buat, jadwalkan, dan publish konten ke berbagai platform sosial.', 'desc_en' => 'Create, schedule, and publish content across multiple social platforms.', 'bullets_id' => ['Multi-platform composer', 'Content calendar', 'RSS auto-post', 'AI caption generator'], 'bullets_en' => ['Multi-platform composer', 'Content calendar', 'RSS auto-post', 'AI caption generator']],
                ],
            ],
            [
                'icon' => 'fas fa-user-tie',
                'group_id' => 'CRM & Pipeline',
                'group_en' => 'CRM & Pipeline',
                'items' => [
                    ['title_id' => 'Sales CRM', 'title_en' => 'Sales CRM', 'desc_id' => 'Kelola pipeline penjualan dan deal dari kontak WhatsApp dengan tampilan Kanban.', 'desc_en' => 'Manage sales pipeline and deals from WhatsApp contacts with Kanban view.', 'bullets_id' => ['Pipeline stages', 'Kanban board', 'Deal tracking', 'Contact timeline'], 'bullets_en' => ['Pipeline stages', 'Kanban board', 'Deal tracking', 'Contact timeline']],
                ],
            ],
            [
                'icon' => 'fas fa-shield-haltered',
                'group_id' => 'Admin & Security',
                'group_en' => 'Admin & Security',
                'items' => [
                    ['title_id' => 'Admin Panel Lengkap', 'title_en' => 'Complete Admin Panel', 'desc_id' => 'Kelola pengguna, paket, transaksi, voucher, dan moderasi konten.', 'desc_en' => 'Manage users, plans, transactions, vouchers, and content moderation.', 'bullets_id' => ['User management', 'Subscription plans', 'Transaction history', 'CMS & blog', 'Payout approvals'], 'bullets_en' => ['User management', 'Subscription plans', 'Transaction history', 'CMS & blog', 'Payout approvals']],
                ],
            ],
        ];
        @endphp

        @foreach($featureGroups as $idx => $group)
        <div class="@if($idx > 0) mt-12 @endif reveal">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-9 h-9 rounded-lg bg-brand-100 flex items-center justify-center">
                    <i class="{{ $group['icon'] }} text-brand-600 text-sm"></i>
                </div>
                <h3 class="font-bold text-xl text-gray-900">{{ $lang === 'id' ? $group['group_id'] : $group['group_en'] }}</h3>
            </div>

            <div class="space-y-5">
                @foreach($group['items'] as $fidx => $item)
                <div class="flex flex-col @if($fidx % 2 === 0) lg:flex-row @else lg:flex-row-reverse @endif gap-6 items-start bg-white border border-gray-200 rounded-2xl p-6 card-lift">
                    <div class="flex-shrink-0 w-full lg:w-80 {{ $fidx % 2 === 1 ? 'lg:ml-auto' : '' }}">
                        <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center h-48 text-gray-400 text-sm">
                            [Screenshot: {{ $lang === 'id' ? $item['title_id'] : $item['title_en'] }}]
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-lg text-gray-900 mb-2">{{ $lang === 'id' ? $item['title_id'] : $item['title_en'] }}</h4>
                        <p class="text-gray-500 text-sm leading-relaxed mb-3">{{ $lang === 'id' ? $item['desc_id'] : $item['desc_en'] }}</p>
                        <ul class="space-y-1">
                            @php $bullets = $lang === 'id' ? $item['bullets_id'] : $item['bullets_en']; @endphp
                            @foreach($bullets as $bullet)
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="fas fa-check-circle text-brand-500 text-xs"></i>
                                <span>{{ $bullet }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section id="cta" class="bg-gradient-to-r from-brand-600 to-brand-800 py-16">
    <div class="max-w-xl mx-auto text-center px-5 text-white reveal">
        @if($lang === 'id')
            <h2 class="text-2xl font-extrabold mb-3">Siap Memulai?</h2>
            <p class="text-brand-200 mb-6 text-lg">Daftar sekarang dan mulai kelola komunikasi bisnis Anda dengan WABot. Gratis untuk dicoba!</p>
        @else
            <h2 class="text-2xl font-extrabold mb-3">Ready to Start?</h2>
            <p class="text-brand-200 mb-6 text-lg">Sign up now and start managing your business communication with WABot. Free to try!</p>
        @endif
        <div class="flex items-center justify-center gap-3">
            <a href="{{ route('register') }}" class="bg-white text-brand-700 px-6 py-3 rounded-xl font-bold hover:shadow-xl transition">
                @if($lang === 'id')Daftar Sekarang@elseSign Up Now@endif
            </a>
            <a href="{{ route('login') }}" class="bg-brand-500/20 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-500/30 transition">
                @if($lang === 'id')Masuk@elseLogin@endif
            </a>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-10 text-sm">
    <div class="max-w-6xl mx-auto px-5 flex flex-col md:flex-row justify-between gap-6">
        <div><span class="text-white font-bold text-lg">WABot</span><p class="mt-1">{{ __('app.tagline') }}</p></div>
        <div class="flex gap-6">
            <a href="/docs" class="hover:text-white">{{ __('nav.docs') }}</a>
            <a href="/blog" class="hover:text-white">{{ __('nav.blog') }}</a>
            <a href="{{ route('login') }}" class="hover:text-white">{{ __('footer.login') }}</a>
        </div>
    </div>
</footer>

<script>
const observer = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible') }), { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
