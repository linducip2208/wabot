<?php

namespace App\Http\Controllers;

use App\Models\WaMediaTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaTemplateController extends Controller
{
    public function index()
    {
        $templates = WaMediaTemplate::where('user_id', Auth::id())
            ->latest()
            ->get();

        $grouped = $templates->groupBy('type');

        return view('media.index', compact('templates', 'grouped'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:image,video,audio,document,sticker,location',
            'media_url' => 'nullable|url|max:2000',
            'caption' => 'nullable|string|max:2000',
            'filename' => 'nullable|string|max:255',
            'mime_type' => 'nullable|string|max:100',
        ]);

        if ($validated['type'] === 'location') {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
        }

        WaMediaTemplate::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'media_url' => $validated['media_url'] ?? '',
            'caption' => $validated['caption'] ?? '',
            'filename' => $validated['filename'] ?? '',
            'mime_type' => $validated['mime_type'] ?? '',
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        return back()->with('success', __('messages.success.media_template_saved'));
    }

    public function update(Request $request, WaMediaTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:image,video,audio,document,sticker,location',
            'media_url' => 'nullable|url|max:2000',
            'caption' => 'nullable|string|max:2000',
            'filename' => 'nullable|string|max:255',
            'mime_type' => 'nullable|string|max:100',
        ]);

        if ($validated['type'] === 'location') {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);
        }

        $template->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'media_url' => $validated['media_url'] ?? '',
            'caption' => $validated['caption'] ?? '',
            'filename' => $validated['filename'] ?? '',
            'mime_type' => $validated['mime_type'] ?? '',
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        return back()->with('success', __('messages.success.media_template_updated'));
    }

    public function destroy(WaMediaTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);
        $template->delete();

        return back()->with('success', __('messages.success.media_template_deleted'));
    }
}
