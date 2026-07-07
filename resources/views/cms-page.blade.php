@extends('layouts.app')
@section('title', $page->title . ' — WABot')
@section('content')

<article class="max-w-3xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">{{ $page->title }}</h1>
        @if($page->updated_at)
        <p class="text-sm text-gray-400 mt-2">Terakhir diperbarui {{ $page->updated_at->format('d M Y') }}</p>
        @endif
    </div>

    <div class="prose max-w-none text-gray-700 leading-relaxed space-y-4">
        {!! $page->content !!}
    </div>

    <div class="mt-12 pt-6 border-t border-gray-200 text-center">
        <a href="{{ route('login') }}" class="text-brand-600 hover:underline text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Kembali ke halaman utama
        </a>
    </div>
</article>

@push('styles')
<style>
    .prose h1 { font-size: 1.75rem; font-weight: 800; color: #111827; margin-top: 1.5rem; margin-bottom: 0.75rem; }
    .prose h2 { font-size: 1.35rem; font-weight: 700; color: #1f2937; margin-top: 1.5rem; margin-bottom: 0.5rem; }
    .prose h3 { font-size: 1.15rem; font-weight: 600; color: #374151; margin-top: 1.25rem; margin-bottom: 0.5rem; }
    .prose p { margin-bottom: 1rem; }
    .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 1rem; }
    .prose li { margin-bottom: 0.25rem; }
    .prose a { color: #2563eb; text-decoration: underline; }
    .prose blockquote { border-left: 4px solid #3b82f6; padding-left: 1rem; color: #6b7280; font-style: italic; margin: 1rem 0; }
    .prose pre { background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; margin-bottom: 1rem; font-size: 13px; }
    .prose code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
    .prose pre code { background: transparent; padding: 0; }
    .prose img { max-width: 100%; border-radius: 0.75rem; }
    .prose table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
    .prose th, .prose td { border: 1px solid #d1d5db; padding: 8px 12px; text-align: left; }
    .prose th { background: #f9fafb; font-weight: 600; }
</style>
@endpush

@endsection
