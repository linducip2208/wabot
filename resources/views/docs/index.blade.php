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
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a'}}}}}</script>
    <script type="application/ld+json">{"@@context":"https://schema.org","@@type":"TechArticle","headline":{{ json_encode($seoMeta['title']) }},"description":{{ json_encode($seoMeta['description']) }},"author":{"@@type":"Organization","name":"WABot"},"url":{{ json_encode($seoMeta['canonical']) }}}</script>
</head>
<body class="bg-white text-gray-900">

@php $L = app()->getLocale(); $id = $L === 'id'; @endphp

<header class="sticky top-0 bg-white/90 backdrop-blur-md border-b border-gray-200 z-40">
    <div class="max-w-6xl mx-auto px-5 flex items-center justify-between h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-extrabold"><i class="fas fa-paper-plane text-brand-500"></i> WABot</a>
        <nav class="flex gap-4 text-sm font-medium overflow-x-auto">
            <a href="/" class="text-gray-600 hover:text-brand-600 whitespace-nowrap">{{ __('nav.home') ?? 'Home' }}</a>
            <a href="#demo" class="text-gray-600 hover:text-brand-600 whitespace-nowrap">{{ $id ? 'Akun Demo' : 'Demo Accounts' }}</a>
            <a href="#menu" class="text-gray-600 hover:text-brand-600 whitespace-nowrap">{{ $id ? 'Struktur Menu' : 'Menu Structure' }}</a>
            <a href="#tutorial" class="text-gray-600 hover:text-brand-600 whitespace-nowrap">{{ $id ? 'Tutorial' : 'Tutorial' }}</a>
            <a href="#fitur" class="text-gray-600 hover:text-brand-600 whitespace-nowrap">{{ $id ? 'Fitur' : 'Features' }}</a>
            <a href="{{ route('login') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg hover:bg-brand-700">{{ $id ? 'Masuk' : 'Login' }}</a>
        </nav>
    </div>
</header>

<section class="py-20 bg-gradient-to-br from-brand-600 to-brand-800 text-white text-center">
    <h1 class="text-4xl font-extrabold mb-4">{{ $id ? 'Dokumentasi Lengkap WABot' : 'WABot Complete Documentation' }}</h1>
    <p class="text-lg text-brand-100 max-w-2xl mx-auto">{{ $id ? 'WhatsApp Marketing SaaS — Auto-Reply, Campaign Bulk, Chat Omni-Channel, AI Agent, Multi-Platform. Panduan langkah demi langkah dari awal hingga mahir.' : 'WhatsApp Marketing SaaS — Auto-Reply, Bulk Campaign, Omni-Channel Chat, AI Agents, Multi-Platform. Step-by-step guide from beginner to expert.' }}</p>
</section>

{{-- Demo Accounts --}}
<section id="demo" class="py-16 max-w-6xl mx-auto px-5">
    <h2 class="text-2xl font-extrabold mb-2 text-center">{{ $id ? 'Akun Demo' : 'Demo Accounts' }}</h2>
    <p class="text-gray-500 text-center mb-8">{{ $id ? 'Gunakan akun ini untuk mencoba semua fitur WABot.' : 'Use these accounts to try all WABot features.' }}</p>
    <div class="overflow-x-auto bg-white rounded-2xl border border-gray-200">
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500"><th class="px-5 py-3">{{ $id ? 'Peran' : 'Role' }}</th><th class="px-5 py-3">Email</th><th class="px-5 py-3">{{ $id ? 'Kata Sandi' : 'Password' }}</th><th class="px-5 py-3">{{ $id ? 'Cakupan' : 'Scope' }}</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                <tr><td class="px-5 py-3 font-semibold text-gray-900">{{ $id ? 'Administrator' : 'Admin' }}</td><td class="px-5 py-3 font-mono text-brand-600">admin@wabot.test</td><td class="px-5 py-3 font-mono">password</td><td class="px-5 py-3 text-xs text-gray-500">{{ $id ? 'Akses penuh: server, user, voucher, transaksi, payout, CMS, blog' : 'Full access: servers, users, vouchers, transactions, payouts, CMS, blog' }}</td></tr>
                <tr><td class="px-5 py-3 font-semibold text-gray-900">{{ $id ? 'Pengguna' : 'User' }}</td><td class="px-5 py-3 font-mono text-brand-600">user@wabot.test</td><td class="px-5 py-3 font-mono">password</td><td class="px-5 py-3 text-xs text-gray-500">{{ $id ? 'Akses standar: chat, kontak, kampanye, auto-reply, sesi, webhook' : 'Standard access: chat, contacts, campaigns, auto-reply, sessions, webhooks' }}</td></tr>
            </tbody>
        </table>
    </div>
    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="inline-block bg-brand-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-700">{{ $id ? 'Masuk ke Demo' : 'Login to Demo' }}</a>
    </div>
</section>

{{-- Menu Structure --}}
<section id="menu" class="py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-5">
        <h2 class="text-2xl font-extrabold mb-2 text-center">{{ $id ? 'Struktur Menu Admin' : 'Admin Menu Structure' }}</h2>
        <p class="text-gray-500 text-center mb-8">{{ $id ? 'Semua grup navigasi dan submenu yang tersedia di dashboard WABot.' : 'All navigation groups and submenus available in the WABot dashboard.' }}</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
            $groups = [
                ['icon'=>'fa-chart-pie','name'=>$id?'Ikhtisar':'Overview','items'=>[$id?'Dashboard':'Dashboard',$id?'Aktivitas':'Activity Feed']],
                ['icon'=>'fa-inbox','name'=>'Inbox','items'=>['Live Chat',$id?'Percakapan':'Conversations',$id?'Antrian':'Queue',$id?'Pesan Terkirim':'Sent Messages','Contacts','Groups']],
                ['icon'=>'fab fa-whatsapp','name'=>'WhatsApp','items'=>[$id?'Sesi / Agen':'Sessions / Agents']],
                ['icon'=>'fa-bullhorn','name'=>$id?'Marketing':'Marketing','items'=>['Broadcast','Campaigns',$id?'Terjadwal':'Scheduled','Drip','A/B Testing','Click Tracking','Templates']],
                ['icon'=>'fa-brain','name'=>'AI Automation','items'=>['AI Agents','Intent','Auto Reply','Knowledge Base','AI API Keys']],
                ['icon'=>'fa-shopping-cart','name'=>'Commerce','items'=>['Catalog','Orders','Buttons','Forms','Media']],
                ['icon'=>'fa-handshake','name'=>'CRM','items'=>['Pipeline','Deals','Kanban','Tags']],
                ['icon'=>'fa-plug','name'=>'Channels','items'=>['WhatsApp Cloud API','WhatsApp Calling','Instagram','Webhooks','Telegram','Facebook','GBM','Discord','SMS','Email','TikTok','LINE','X/Twitter']],
                ['icon'=>'fa-users','name'=>$id?'Tim':'Team','items'=>['Shared Inbox','Members','SLA','SLA Settings']],
                ['icon'=>'fa-chart-line','name'=>'Analytics','items'=>[$id?'Dashboard Analytics':'Analytics Dashboard','Sentiment','Ratings',$id?'Log Aktivitas':'Activity Logs']],
                ['icon'=>'fa-puzzle-piece','name'=>$id?'Integrasi':'Integrations','items'=>['E-Commerce','Google Sheets']],
                ['icon'=>'fa-magic','name'=>'AI Studio','items'=>['Content','Image','Planner','Best Time']],
                ['icon'=>'fa-share-alt','name'=>'Social Media','items'=>['Composer','Calendar','Queue','Drafts','RSS','Captions']],
                ['icon'=>'fa-cog','name'=>$id?'Pengaturan':'Settings','items'=>['Server','Subscription','Plans','API Tokens','Payout']],
                ['icon'=>'fa-shield-alt','name'=>'Admin','items'=>['Users','Plans','Vouchers','Transactions','Shortener','CMS','Blog','Payout Approval','Credit Packs','Coupons']],
                ['icon'=>'fa-gem','name'=>$id?'Dapatkan':'Earn','items'=>['Credits','Affiliate']],
            ];
            @endphp
            @foreach($groups as $g)
            <div class="bg-white rounded-xl border border-gray-200 p-4 card-lift">
                <h3 class="font-bold text-gray-900 mb-2"><i class="fas {{ $g['icon'] }} text-brand-500 mr-1 text-xs"></i> {{ $g['name'] }}</h3>
                <ul class="space-y-1">@foreach($g['items'] as $item)<li class="text-xs text-gray-500 flex items-center gap-1"><i class="fas fa-circle text-[4px] text-gray-300"></i> {{ $item }}</li>@endforeach</ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Tutorial --}}
<section id="tutorial" class="py-16 max-w-6xl mx-auto px-5">
    <h2 class="text-2xl font-extrabold mb-2 text-center">{{ $id ? 'Tutorial Langkah demi Langkah' : 'Step by Step Tutorial' }}</h2>
    <p class="text-gray-500 text-center mb-8">{{ $id ? 'Ikuti 15 fase di bawah ini untuk menguasai WABot dari nol.' : 'Follow the 15 phases below to master WABot from scratch.' }}</p>
    <div class="space-y-4">
        @php
        $phases = [
            ['num'=>1,'icon'=>'fa-user-plus','title'=>$id?'Pendaftaran & Login':'Registration & Login','en'=>'Create an account, verify email, login to dashboard.','id'=>'Daftar akun, verifikasi email, masuk ke dashboard.'],
            ['num'=>2,'icon'=>'fa-server','title'=>$id?'Setup Server':'Server Setup','en'=>'Add Baileys server in Settings > Server. Configure host, port, and API key.','id'=>'Tambah server Baileys di Settings > Server. Konfigurasi host, port, dan API key.'],
            ['num'=>3,'icon'=>'fa-qrcode','title'=>$id?'Koneksi WhatsApp':'Connect WhatsApp','en'=>'Create a session in Sessions/Agents, select server, scan QR code with WhatsApp mobile.','id'=>'Buat sesi di Sessions/Agents, pilih server, scan QR code dengan WhatsApp HP.'],
            ['num'=>4,'icon'=>'fa-address-book','title'=>$id?'Manajemen Kontak':'Contact Management','en'=>'Add contacts manually or import CSV. Create groups and tags for segmentation.','id'=>'Tambah kontak manual atau import CSV. Buat grup dan tag untuk segmentasi.'],
            ['num'=>5,'icon'=>'fa-reply-all','title'=>'Auto-Reply','en'=>'Set up keyword triggers, welcome messages, and fallback replies with spintax support.','id'=>'Atur trigger kata kunci, pesan sambutan, dan balasan fallback dengan dukungan spintax.'],
            ['num'=>6,'icon'=>'fa-bullhorn','title'=>$id?'Kampanye & Broadcast':'Campaigns & Broadcast','en'=>'Create mass message campaigns with anti-ban delay. Schedule or send immediately to contacts/groups.','id'=>'Buat kampanye pesan massal dengan delay anti-ban. Jadwalkan atau kirim langsung ke kontak/grup.'],
            ['num'=>7,'icon'=>'fa-brain','title'=>$id?'AI Agents & Knowledge':'AI Agents & Knowledge','en'=>'Add AI API keys, create AI agents with roles and trigger keywords. Upload FAQ as knowledge base.','id'=>'Tambah AI API keys, buat AI agent dengan role dan trigger keyword. Upload FAQ sebagai knowledge base.'],
            ['num'=>8,'icon'=>'fa-project-diagram','title'=>'Flow Builder','en'=>'Design visual chatbot flows with message, condition, AI, wait, and booking nodes.','id'=>'Desain alur chatbot visual dengan node pesan, kondisi, AI, tunggu, dan booking.'],
            ['num'=>9,'icon'=>'fa-plug','title'=>$id?'Multi-Channel Setup':'Multi-Channel Setup','en'=>'Connect Meta Cloud API, Instagram, Telegram, Facebook, Discord, GBM, SMS, Email, and more.','id'=>'Hubungkan Meta Cloud API, Instagram, Telegram, Facebook, Discord, GBM, SMS, Email, dan lainnya.'],
            ['num'=>10,'icon'=>'fa-users','title'=>$id?'Team Inbox & SLA':'Team Inbox & SLA','en'=>'Add team members, auto-assign conversations, set SLA response/resolution targets.','id'=>'Tambah anggota tim, auto-assign percakapan, atur target SLA respons/penyelesaian.'],
            ['num'=>11,'icon'=>'fa-shopping-bag','title'=>'E-Commerce','en'=>'Install WooCommerce plugin, connect store, auto-send order notifications via WhatsApp.','id'=>'Install plugin WooCommerce, hubungkan toko, kirim notifikasi order otomatis via WhatsApp.'],
            ['num'=>12,'icon'=>'fa-calendar-alt','title'=>$id?'Social Publishing':'Social Publishing','en'=>'Schedule social media posts across platforms, RSS auto-post, caption library.','id'=>'Jadwalkan postingan sosial media lintas platform, RSS auto-post, library caption.'],
            ['num'=>13,'icon'=>'fa-magic','title'=>'AI Content Studio','en'=>'Generate content, images, content plans, and find best posting times with AI.','id'=>'Generate konten, gambar, rencana konten, dan temukan waktu posting terbaik dengan AI.'],
            ['num'=>14,'icon'=>'fa-chart-bar','title'=>$id?'Analytics & Reports':'Analytics & Reports','en'=>'View sentiment analysis, ratings, per-channel stats, and activity logs.','id'=>'Lihat analisis sentimen, rating, statistik per-channel, dan log aktivitas.'],
            ['num'=>15,'icon'=>'fa-coins','title'=>$id?'Monetisasi':'Monetization','en'=>'Buy credit packs, join affiliate program, redeem coupons, request payouts.','id'=>'Beli paket kredit, ikut program afiliasi, tukar kupon, ajukan pencairan.'],
        ];
        @endphp
        @foreach($phases as $p)
        <div class="reveal bg-white border border-gray-200 rounded-2xl p-6 card-lift">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center flex-shrink-0"><i class="fas {{ $p['icon'] }} text-brand-500 text-lg"></i></div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="bg-brand-600 text-white text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $p['num'] }}</span>
                        <h3 class="font-bold text-gray-900">{{ $p['title'] }}</h3>
                    </div>
                    <p class="text-sm text-gray-600">{{ $id ? $p['id'] : $p['en'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section id="cta" class="py-20 bg-gradient-to-br from-brand-700 to-brand-900 text-white text-center">
    <h2 class="text-3xl font-extrabold mb-3">{{ $id ? 'Siap Memulai?' : 'Ready to Start?' }}</h2>
    <p class="text-brand-200 mb-8 max-w-md mx-auto">{{ $id ? 'Kelola semua komunikasi bisnis dalam satu dashboard. Daftar gratis, langsung bisa dipakai.' : 'Manage all business communications in one dashboard. Sign up free, ready to use.' }}</p>
    <div class="flex justify-center gap-4">
        <a href="{{ route('register') }}" class="bg-white text-brand-700 px-8 py-3.5 rounded-xl font-bold hover:shadow-xl transition">{{ $id ? 'Daftar Sekarang' : 'Sign Up Now' }}</a>
        <a href="{{ route('login') }}" class="bg-brand-500/20 text-white px-8 py-3.5 rounded-xl font-semibold hover:bg-brand-500/30 transition">{{ $id ? 'Masuk' : 'Login' }}</a>
    </div>
</section>

<footer class="bg-gray-900 text-gray-400 py-10 text-sm">
    <div class="max-w-6xl mx-auto px-5 flex flex-col md:flex-row justify-between gap-6">
        <div><span class="text-white font-bold text-lg">WABot</span><p class="mt-1">{{ __('app.tagline') }}</p></div>
        <div class="flex gap-6"><a href="/docs" class="hover:text-white">{{ __('nav.docs') }}</a><a href="/blog" class="hover:text-white">{{ __('nav.blog') }}</a><a href="{{ route('login') }}" class="hover:text-white">{{ __('footer.login') }}</a></div>
    </div>
</footer>

<script>
const observer = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible') }), { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
