<?php

namespace App\Http\Controllers;

use App\Models\WaMessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = WaMessageTemplate::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'format' => 'required|in:whatsapp,text,markdown',
        ]);

        WaMessageTemplate::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'message' => $validated['message'],
            'format' => $validated['format'],
        ]);

        return back()->with('success', __('messages.success.template_added'));
    }

    public function update(Request $request, WaMessageTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:10000',
            'format' => 'required|in:whatsapp,text,markdown',
        ]);

        $template->update($validated);

        return back()->with('success', __('messages.success.template_updated'));
    }

    public function destroy(WaMessageTemplate $template)
    {
        abort_if($template->user_id !== Auth::id(), 403);
        $template->delete();

        return back()->with('success', __('messages.success.template_deleted'));
    }
}
