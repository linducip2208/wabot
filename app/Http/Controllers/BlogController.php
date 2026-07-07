<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $posts = BlogPost::with('category', 'author')
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => function ($q) {
            $q->where('is_published', true)
              ->whereNotNull('published_at')
              ->where('published_at', '<=', now());
        }])->get();

        $seoMeta = [
            'title' => 'Blog — WABot WhatsApp Marketing SaaS',
            'description' => 'Tips, tutorial, dan update terbaru seputar WhatsApp Marketing, otomatisasi pesan, dan strategi komunikasi bisnis.',
            'canonical' => url('/blog'),
        ];

        return view('blog.index', compact('posts', 'categories', 'seoMeta'));
    }

    public function show($slug)
    {
        $post = BlogPost::with('category', 'author')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $relatedPosts = BlogPost::with('category', 'author')
            ->where('is_published', true)
            ->where('id', '!=', $post->id)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->when($post->category_id, fn($q) => $q->where('category_id', $post->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        $seoMeta = [
            'title' => ($post->meta_title ?: $post->title) . ' — Blog WABot',
            'description' => $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)),
            'canonical' => url('/blog/' . $post->slug),
        ];

        return view('blog.show', compact('post', 'relatedPosts', 'seoMeta'));
    }

    public function category($slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::with('category', 'author')
            ->where('category_id', $category->id)
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => function ($q) {
            $q->where('is_published', true)
              ->whereNotNull('published_at')
              ->where('published_at', '<=', now());
        }])->get();

        $seoMeta = [
            'title' => 'Kategori: ' . $category->name . ' — Blog WABot',
            'description' => 'Artikel dalam kategori ' . $category->name . ' — WABot WhatsApp Marketing SaaS.',
            'canonical' => url('/blog/category/' . $category->slug),
        ];

        return view('blog.index', compact('posts', 'categories', 'seoMeta'));
    }
}
