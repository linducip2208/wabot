<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('category', 'author')->latest()->get();
        $categories = BlogCategory::all();
        return view('admin.blog.index', compact('posts', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
        ]);

        BlogPost::create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'content' => $data['content'],
            'excerpt' => $data['excerpt'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'author_id' => auth()->id(),
            'is_published' => $data['is_published'] ?? false,
            'published_at' => ($data['is_published'] ?? false) ? now() : null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        return back()->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function update(Request $request, BlogPost $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'featured_image' => 'nullable|string|max:500',
            'category_id' => 'nullable|exists:blog_categories,id',
            'is_published' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:255',
        ]);

        $updateData = [
            'title' => $data['title'],
            'slug' => $data['slug'] ?: Str::slug($data['title']),
            'content' => $data['content'],
            'excerpt' => $data['excerpt'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ];

        if (($data['is_published'] ?? false) && !$post->published_at) {
            $updateData['published_at'] = now();
        }

        $post->update($updateData);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();
        return back()->with('success', 'Artikel dihapus.');
    }

    // Categories
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug',
        ]);

        BlogCategory::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, BlogCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $category->id,
        ]);

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(BlogCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}
