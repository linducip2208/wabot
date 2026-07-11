<?php

namespace App\Http\Controllers;

use App\Models\WaAiImageJob;
use App\Services\AiImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiImageController extends Controller
{
    public function index()
    {
        $jobs = WaAiImageJob::where('user_id', Auth::id())
            ->latest()
            ->paginate(12);

        return view('ai-image.index', compact('jobs'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:2000',
            'style' => 'nullable|string|in:photorealistic,illustration,anime,3d,logo',
            'size' => 'nullable|string|in:square,landscape,portrait',
            'count' => 'nullable|integer|min:1|max:4',
        ]);

        $job = WaAiImageJob::create([
            'user_id' => Auth::id(),
            'prompt' => $validated['prompt'],
            'style' => $validated['style'] ?? 'photorealistic',
            'size' => $validated['size'] ?? 'square',
            'count' => $validated['count'] ?? 1,
            'status' => 'pending',
        ]);

        $imageService = app(AiImageService::class);
        $imageService->generateAndStore($job);

        if ($job->fresh()->status === 'failed') {
            return back()->with('error', __('aiimage.generation_failed'))->withInput();
        }

        return redirect()->route('ai-image.index')->with('success', __('aiimage.generation_success', ['count' => $job->count]));
    }

    public function list()
    {
        $jobs = WaAiImageJob::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->latest()
            ->paginate(12);

        return view('ai-image.index', compact('jobs'));
    }
}
