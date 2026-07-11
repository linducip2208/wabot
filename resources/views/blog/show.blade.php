<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoMeta['title'] }}</title>
    <meta name="description" content="{{ $seoMeta['description'] }}">
    <meta property="og:title" content="{{ $seoMeta['title'] }}">
    <meta property="og:description" content="{{ $seoMeta['description'] }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $seoMeta['canonical'] }}">
    @if($post->featured_image)
    <meta property="og:image" content="{{ $post->featured_image }}">
    @endif
    <meta property="article:published_time" content="{{ $post->published_at->toIso8601String() }}">
    @if($post->author)
    <meta property="article:author" content="{{ $post->author->name }}">
    @endif
    <link rel="canonical" href="{{ $seoMeta['canonical'] }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .prose h1 { font-size: 1.5rem; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #111827; }
        .prose h2 { font-size: 1.25rem; font-weight: 700; margin-top: 1.25rem; margin-bottom: 0.5rem; color: #1f2937; }
        .prose h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.5rem; color: #374151; }
        .prose p { margin-bottom: 1rem; line-height: 1.75; }
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
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "{{ $post->title }}",
        "description": "{{ $seoMeta['description'] }}",
        "datePublished": "{{ $post->published_at->toIso8601String() }}",
        "dateModified": "{{ $post->updated_at->toIso8601String() }}",
        @if($post->author)
        "author": { "@type": "Person", "name": "{{ $post->author->name }}" },
        @endif
        "url": "{{ $seoMeta['canonical'] }}"
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

{{-- Article --}}
<article class="max-w-4xl mx-auto px-5 py-10 lg:py-16">
    {{-- Header --}}
    <header class="mb-8">
        @if($post->category)
        <a href="{{ url('/blog/category/' . $post->category->slug) }}" class="text-xs font-semibold text-brand-600 uppercase tracking-wide">{{ $post->category->name }}</a>
        @endif
        <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight mt-2 mb-4">{{ $post->title }}</h1>
        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
            @if($post->author)
            <span class="flex items-center gap-1.5">
                <div class="w-7 h-7 rounded-full bg-brand-500 flex items-center justify-center text-white text-[11px] font-bold">{{ strtoupper(substr($post->author->name, 0, 2)) }}</div>
                {{ $post->author->name }}
            </span>
            @endif
            <span class="flex items-center gap-1.5"><i class="fas fa-calendar text-gray-400"></i> {{ $post->published_at->format('d M Y') }}</span>
        </div>
    </header>

    @if($post->featured_image)
    <div class="mb-8 rounded-xl overflow-hidden">
        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full max-h-96 object-cover">
    </div>
    @endif

    {{-- Content --}}
    <div class="prose max-w-none text-gray-700 leading-relaxed">
        {!! $post->content !!}
    </div>

    {{-- Share --}}
    <div class="mt-12 pt-6 border-t border-gray-200 flex items-center gap-3">
        <span class="text-sm text-gray-500 font-medium">{{ __('blog.share') }}</span>
        <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . url('/blog/' . $post->slug)) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-emerald-500 text-white flex items-center justify-center hover:bg-emerald-600 transition" title="WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/blog/' . $post->slug)) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700 transition" title="Facebook">
            <i class="fab fa-facebook-f text-sm"></i>
        </a>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('/blog/' . $post->slug)) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="w-9 h-9 rounded-lg bg-gray-800 text-white flex items-center justify-center hover:bg-black transition" title="Twitter/X">
            <i class="fab fa-x-twitter text-sm"></i>
        </a>
    </div>
</article>

{{-- Related Posts --}}
@if(isset($relatedPosts) && $relatedPosts->count())
<section class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-5">
        <h2 class="text-xl font-extrabold mb-6">{{ __('blog.related_articles') }}</h2>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($relatedPosts as $rel)
            <a href="{{ url('/blog/' . $rel->slug) }}" class="bg-white rounded-xl border border-gray-200 p-5 card-lift block hover:border-brand-200 transition">
                <h3 class="font-semibold text-gray-900 mb-2 text-sm leading-tight">{{ $rel->title }}</h3>
                <p class="text-xs text-gray-500">{{ $rel->published_at->format('d M Y') }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

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
