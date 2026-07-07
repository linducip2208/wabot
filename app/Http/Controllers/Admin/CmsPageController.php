<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsPageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::latest()->get();
        return view('admin.pages.index', compact('pages'));
    }

    public function builder(Request $request, CmsPage $page = null)
    {
        // $page will be null when accessing /admin/pages/builder (new page)
        // $page will be populated when accessing /admin/pages/{page}/builder (edit)
        return view('admin.pages.builder', ['page' => $page]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'nullable|string|max:255|unique:cms_pages,slug',
            'content' => 'required|string',
        ]);

        $page = CmsPage::create([
            'title'   => $data['title'],
            'slug'    => $data['slug'] ?: Str::slug($data['title']),
            'content' => $data['content'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'redirect' => route('admin.pages.builder', $page),
                'message' => 'Halaman dibuat.',
            ]);
        }

        return back()->with('success', 'Halaman CMS berhasil ditambahkan.');
    }

    public function update(Request $request, CmsPage $page)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'nullable|string|max:255|unique:cms_pages,slug,' . $page->id,
            'content' => 'required|string',
        ]);

        $page->update([
            'title'   => $data['title'],
            'slug'    => $data['slug'] ?: Str::slug($data['title']),
            'content' => $data['content'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Halaman disimpan.']);
        }

        return back()->with('success', 'Halaman CMS berhasil diperbarui.');
    }

    public function destroy(CmsPage $page)
    {
        $page->delete();
        return back()->with('success', 'Halaman CMS dihapus.');
    }

    public function show($slug)
    {
        $page = CmsPage::where('slug', $slug)->firstOrFail();
        return view('cms-page', compact('page'));
    }
}
