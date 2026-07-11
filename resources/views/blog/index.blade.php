<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoMeta['title'] ?? 'Blog — WABot' }}</title>
    <meta name="description" content="{{ $seoMeta['description'] ?? 'Blog WABot — tips dan update WhatsApp Marketing' }}">
    <meta property="og:title" content="{{ $seoMeta['title'] ?? 'Blog — WABot' }}">
    <meta property="og:description" content="{{ $seoMeta['description'] ?? 'Blog WABot — tips dan update WhatsApp Marketing' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $seoMeta['canonical'] ?? url()->current() }}">
    <link rel="canonical" href="{{ $seoMeta['canonical'] ?? url()->current() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .card-lift { transition: transform .25s, box-shadow .25s; }
        .card-lift:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -8px rgba(0,0,0,.12); }
        .prose h1 { font-size: 1.5rem; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .prose h2 { font-size: 1.25rem; font-weight: 700; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .prose h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.5rem; }
        .prose p { margin-bottom: 1rem; }
        .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        .prose li { margin-bottom: 0.25rem; }
        .prose a { color: #2563eb; text-decoration: underline; }
        .prose blockquote { border-left: 4px solid #3b82f6; padding-left: 1rem; color: #6b7280; font-style: italic; margin: 1rem 0; }
        .prose pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; margin-bottom: 1rem; font-size: 13px; }
        .prose code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
        .prose pre code { background: transparent; padding: 0; }
        .prose img { max-width: 100%; border-radius: 0.75rem; }
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
</head>
<body class="bg-white text-gray-900">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-gray-100">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-5 h-16">
        <a href="/" class="flex items-center gap-2 text-xl font-extrabold tracking-tight"><i class="fas fa-paper-plane text-brand-500"></i> WABot</a>
        <div class="flex items-center gap-4">
            <a href="/docs" class="text-sm text-gray-600 hover:text-brand-600 font-medium">{{ __('nav.docs') }}</a>
            <a href="/blog" class="text-sm text-brand-600 font-semibold">{{ __('nav.blog') }}</a>
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-brand-600 font-medium">{{ __('nav.login') }}</a>
            <a href="{{ route('register') }}" class="bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-brand-700 transition">{{ __('nav.register') }}</a>
        </div>
    </div>
</nav>

{{-- Header --}}
<section class="bg-gradient-to-r from-brand-600 to-brand-800 py-12 lg:py-16">
    <div class="max-w-6xl mx-auto px-5 text-white text-center">
        <h1 class="text-3xl lg:text-4xl font-extrabold mb-3">Blog WABot</h1>
        <p class="text-brand-200 text-lg">{{ __('blog.hero_description') }}</p>
    </div>
</section>

{{-- Content --}}
<div class="max-w-6xl mx-auto px-5 py-10">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Posts --}}
        <div class="flex-1 min-w-0">
            @if(isset($posts) && $posts->count())
            <div class="grid md:grid-cols-2 gap-5">
                @foreach($posts as $post)
                <article class="bg-white rounded-xl border border-gray-200 overflow-hidden card-lift">
                    @if($post->featured_image)
                    <a href="{{ url('/blog/' . $post->slug) }}">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                    </a>
                    @endif
                    <div class="p-5">
                        @if($post->category)
                        <a href="{{ url('/blog/category/' . $post->category->slug) }}" class="text-[11px] font-semibold text-brand-600 uppercase tracking-wide">{{ $post->category->name }}</a>
                        @endif
                        <a href="{{ url('/blog/' . $post->slug) }}" class="block mt-1">
                            <h2 class="text-lg font-bold text-gray-900 leading-tight hover:text-brand-600 transition">{{ $post->title }}</h2>
                        </a>
                        @if($post->excerpt)
                        <p class="text-sm text-gray-500 mt-2 leading-relaxed">{{ $post->excerpt }}</p>
                        @endif
                        <div class="flex items-center gap-3 mt-4 text-xs text-gray-400">
                            @if($post->author)
                            <span class="flex items-center gap-1"><i class="fas fa-user"></i> {{ $post->author->name }}</span>
                            @endif
                            <span><i class="fas fa-calendar"></i> {{ $post->published_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($posts->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="flex items-center gap-1">
                    @if($posts->onFirstPage())
                        <span class="px-3 py-2 rounded-lg text-gray-300 text-sm"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $posts->previousPageUrl() }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-600 text-sm"><i class="fas fa-chevron-left"></i></a>
                    @endif
                    @foreach(range(1, $posts->lastPage()) as $page)
                        <a href="{{ $posts->url($page) }}" class="px-3 py-2 rounded-lg text-sm font-medium {{ $posts->currentPage() === $page ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ $page }}</a>
                    @endforeach
                    @if($posts->hasMorePages())
                        <a href="{{ $posts->nextPageUrl() }}" class="px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-600 text-sm"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span class="px-3 py-2 rounded-lg text-gray-300 text-sm"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
            @endif
            @else
            <div class="text-center py-20 text-gray-500">
                <i class="fas fa-newspaper text-5xl text-gray-300 mb-4 block"></i>
                <p class="text-lg">{{ __('blog.no_articles') }}</p>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="w-full lg:w-72 flex-shrink-0 space-y-5">
            {{-- Categories --}}
            @if(isset($categories) && $categories->count())
            <div class="bg-white border border-gray-200 rounded-xl p-5">
                <h3 class="font-bold text-gray-900 mb-3">{{ __('common.category') }}</h3>
                <ul class="space-y-1.5">
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ url('/blog/category/' . $cat->slug) }}" class="flex items-center justify-between text-sm text-gray-600 hover:text-brand-600 py-1">
                            <span>{{ $cat->name }}</span>
                            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ $cat->posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- CTA --}}
            <div class="bg-gradient-to-br from-brand-600 to-brand-800 rounded-xl p-5 text-white text-center">
                <h3 class="font-bold mb-2">{{ __('cta.heading') }}</h3>
                <p class="text-brand-200 text-sm mb-4">{{ __('blog.cta_subtitle') }}</p>
                <a href="{{ route('register') }}" class="block w-full bg-white text-brand-700 rounded-xl py-2.5 text-sm font-semibold hover:shadow-lg transition">{{ __('nav.register') }}</a>
            </div>
        </div>
    </div>
</div>

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

</body>
</html>
