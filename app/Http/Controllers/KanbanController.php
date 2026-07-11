<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use App\Models\WaContactTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function index()
    {
        $tags = WaContactTag::where('user_id', Auth::id())->get();
        $contacts = WaContact::where('user_id', Auth::id())
            ->with(['contactTags', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->latest()
            ->get()
            ->groupBy(fn($c) => $c->contactTags->first()?->name ?? 'Belum Ditandai');

        return view('kanban.index', compact('tags', 'contacts'));
    }

    public function move(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|exists:wa_contacts,id',
            'tag_id' => 'nullable|exists:wa_contact_tags,id',
        ]);

        $contact = WaContact::where('user_id', Auth::id())->findOrFail($validated['contact_id']);

        $contact->contactTags()->detach();

        if ($validated['tag_id']) {
            $contact->contactTags()->attach($validated['tag_id']);
        }

        return response()->json(['ok' => true]);
    }
}
