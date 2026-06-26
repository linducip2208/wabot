<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WABot — WhatsApp Marketing SaaS</title>
    <link rel="canonical" href="https://wabot.whitelabel.co.id">
    <meta property="og:title" content="WABot — WhatsApp Marketing SaaS">
    <meta property="og:description" content="Auto-Reply, Campaign Bulk, Chat Omni-Channel. Kelola WhatsApp bisnis dalam satu dashboard.">
    <meta property="og:url" content="https://wabot.whitelabel.co.id">
    <meta name="description" content="WABot — WhatsApp Marketing SaaS. Auto-reply keyword, campaign bulk, chat omni-channel. Kelola semua WhatsApp bisnis Anda.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>body{font-family:'Inter',sans-serif;scroll-behavior:smooth}.reveal{opacity:0;transform:translateY(30px);transition:opacity .7s,transform .7s cubic-bezier(.16,1,.3,1)}.reveal.visible{opacity:1;transform:translateY(0)}</style>
    <script>tailwind.config={theme:{extend:{colors:{brand:{50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a'}}}}}</script>
</head>
<body class="bg-white text-gray-900">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-5 h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-extrabold tracking-tight"><i class="fas fa-paper-plane text-brand-500"></i> WABot</a>
        <div class="flex items-center gap-4">
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-brand-600 font-medium">Masuk</a>
            <a href="{{ route('register') }}" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">Daftar Gratis</a>
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="max-w-6xl mx-auto px-5 py-20 lg:py-28">
    <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <div class="inline-flex items-center gap-2 bg-brand-50 text-brand-700 text-xs font-semibold px-3 py-1 rounded-full mb-6"><span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span> WhatsApp Marketing SaaS</div>
            <h1 class="text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight mb-4">Auto-Reply, Campaign Bulk, Chat Omni-Channel</h1>
            <p class="text-gray-500 text-lg leading-relaxed mb-8 max-w-lg">Kelola semua komunikasi WhatsApp bisnis Anda dalam satu dashboard. Balas otomatis, kirim campaign massal, pantau percakapan real-time.</p>
            <div class="flex items-center gap-3">
                <a href="{{ route('register') }}" class="bg-brand-600 text-white px-6 py-3 rounded-xl font-semibold hover:bg-brand-700 shadow-lg shadow-brand-500/25 transition flex items-center gap-2">Coba Gratis <i class="fas fa-arrow-right"></i></a>
                <a href="#features" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-200 transition">Fitur</a>
            </div>
        </div>
        <div class="relative">
            <div class="bg-gradient-to-br from-brand-500 to-brand-700 rounded-3xl p-1 shadow-2xl">
                <div class="bg-gray-900 rounded-[20px] overflow-hidden">
                    <div class="flex items-center gap-1.5 px-4 py-3 border-b border-white/10"><span class="w-3 h-3 rounded-full bg-red-400"></span><span class="w-3 h-3 rounded-full bg-amber-400"></span><span class="w-3 h-3 rounded-full bg-emerald-400"></span></div>
                    <div class="p-4 space-y-3">
                        <div class="flex gap-2">
                            <div class="w-full bg-gray-800 rounded-lg p-3">
                                <div class="w-20 h-2 bg-brand-500/30 rounded mb-2"></div>
                                <div class="w-32 h-3 bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        <div class="flex gap-2"><div class="w-8 h-8 rounded-full bg-violet-500/30"></div><div class="flex-1"><div class="w-24 h-2 bg-gray-700 rounded mb-1"></div><div class="w-40 h-4 bg-brand-500/20 rounded-lg"></div></div></div>
                        <div class="flex gap-2"><div class="w-8 h-8 rounded-full bg-emerald-500/30"></div><div class="flex-1"><div class="w-16 h-2 bg-gray-700 rounded mb-1"></div><div class="w-28 h-4 bg-gray-700 rounded-lg"></div></div></div>
                        <div class="flex justify-end"><div class="w-48 h-4 bg-emerald-500/20 rounded-lg"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="bg-gray-50 py-20">
    <div class="max-w-6xl mx-auto px-5">
        <div class="text-center mb-12"><h2 class="text-3xl font-extrabold mb-3">Fitur Lengkap</h2><p class="text-gray-500 max-w-lg mx-auto">Semua yang Anda butuhkan untuk WhatsApp marketing</p></div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
            @php $features = [
                ['fas fa-robot', 'Auto-Reply Keyword', 'Balas otomatis berdasarkan keyword dengan spintax {Halo|Hai}'],
                ['fas fa-bullhorn', 'Campaign Bulk', 'Kirim pesan massal ke ribuan kontak dengan delay anti-ban'],
                ['fas fa-comments', 'Chat Omni-Channel', 'Inbox real-time, lihat & balas semua percakapan'],
                ['fas fa-clock', 'Jadwal Berulang', 'Welcome message, daily/weekly/monthly auto-send'],
                ['fas fa-chart-bar', 'Dashboard Grafik', 'Pantau aktivitas pesan dengan chart interaktif'],
                ['fas fa-mobile-alt', 'Multi-Agen', 'Hubungkan banyak nomor WhatsApp sekaligus'],
                ['fas fa-layer-group', 'Grup Kontak', 'Kelompokkan kontak untuk targeting campaign'],
                ['fas fa-key', 'API Token', 'Integrasi dengan tools eksternal via REST API'],
                ['fas fa-cloud-upload-alt', 'Import CSV', 'Upload kontak dari file CSV sekali klik'],
            ]; @endphp
            @foreach($features as [$icon, $title, $desc])
            <div class="bg-white rounded-2xl border border-gray-100 p-6 card-lift reveal">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center mb-4"><i class="{{ $icon }} text-brand-500 text-lg"></i></div>
                <h3 class="font-bold mb-2">{{ $title }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing --}}
<section class="py-20">
    <div class="max-w-5xl mx-auto px-5 text-center mb-12"><h2 class="text-3xl font-extrabold mb-3">Paket Harga</h2><p class="text-gray-500">Mulai gratis, upgrade sesuai kebutuhan</p></div>
    <div class="max-w-5xl mx-auto px-5 grid md:grid-cols-3 gap-5">
        @foreach(\App\Models\Plan::where('is_active',true)->orderBy('sort_order')->get() as $p)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 card-lift flex flex-col {{ $p->slug === 'growth' ? 'ring-2 ring-brand-200 shadow-lg' : '' }}">
            <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center mb-4"><i class="fas {{ $p->slug==='free'?'fa-gift':($p->slug==='growth'?'fa-rocket':'fa-building') }} text-brand-500 text-lg"></i></div>
            <h3 class="text-xl font-bold">{{ $p->name }}</h3>
            <div class="mt-2 mb-4"><span class="text-3xl font-extrabold">{{ $p->price > 0 ? 'Rp '.number_format($p->price,0,',','.') : 'Gratis' }}</span>{{ $p->price > 0 ? '<span class="text-sm text-gray-500">/bln</span>' : '' }}</div>
            <div class="space-y-2 mb-6 flex-1 text-sm text-gray-600">
                <div><i class="fas fa-check text-emerald-500 mr-2 text-xs"></i> {{ $p->max_sessions }} Sesi WA</div>
                <div><i class="fas fa-check text-emerald-500 mr-2 text-xs"></i> {{ number_format($p->max_contacts) }} Kontak</div>
                <div><i class="fas fa-check text-emerald-500 mr-2 text-xs"></i> {{ $p->max_autoreplies }} Auto-Reply</div>
                <div><i class="fas fa-check text-emerald-500 mr-2 text-xs"></i> {{ number_format($p->max_campaign_recipients) }} Penerima/Campaign</div>
            </div>
            <a href="{{ route('register') }}" class="w-full text-center bg-brand-600 text-white rounded-xl py-3 font-semibold text-sm hover:bg-brand-700 transition">Daftar Gratis</a>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA --}}
<section class="bg-gradient-to-r from-brand-600 to-brand-800 py-20">
    <div class="max-w-2xl mx-auto text-center px-5 text-white">
        <h2 class="text-3xl font-extrabold mb-3">Siap Otomatiskan WhatsApp?</h2>
        <p class="text-brand-200 mb-8 text-lg">Daftar gratis, langsung bisa dipakai. No credit card.</p>
        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-brand-700 px-8 py-3.5 rounded-xl font-bold hover:shadow-xl transition">Mulai Sekarang <i class="fas fa-arrow-right"></i></a>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-12 text-sm">
    <div class="max-w-6xl mx-auto px-5 flex flex-col md:flex-row justify-between gap-6">
        <div><span class="text-white font-bold text-lg">WABot</span><p class="mt-1">WhatsApp Marketing SaaS</p></div>
        <div class="flex gap-8"><div><a href="{{ route('login') }}" class="hover:text-white">Login</a></div><div><a href="{{ route('register') }}" class="hover:text-white">Daftar</a></div></div>
    </div>
</footer>

<script>
const observer = new IntersectionObserver(entries => entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible') }), { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>
