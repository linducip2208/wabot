<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UrlShortener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ShortenerController extends Controller
{
    public function index()
    {
        $shorteners = UrlShortener::latest()->get();
        return view('admin.shorteners.index', compact('shorteners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'base_url'  => 'required|url|max:500',
            'api_key'   => 'required|string|max:500',
        ]);

        UrlShortener::create([
            'name'               => $data['name'],
            'base_url'           => $data['base_url'],
            'api_key_encrypted'  => Crypt::encryptString($data['api_key']),
        ]);

        return back()->with('success', 'URL Shortener berhasil ditambahkan.');
    }

    public function update(Request $request, UrlShortener $shortener)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'base_url'  => 'required|url|max:500',
            'api_key'   => 'nullable|string|max:500',
        ]);

        $updateData = [
            'name'     => $data['name'],
            'base_url' => $data['base_url'],
        ];

        if (!empty($data['api_key'])) {
            $updateData['api_key_encrypted'] = Crypt::encryptString($data['api_key']);
        }

        $shortener->update($updateData);

        return back()->with('success', 'URL Shortener berhasil diperbarui.');
    }

    public function destroy(UrlShortener $shortener)
    {
        $shortener->delete();
        return back()->with('success', 'URL Shortener dihapus.');
    }
}
