<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use App\Models\WaContactTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactTagController extends Controller
{
    public function index()
    {
        $tags = WaContactTag::where('user_id', Auth::id())
            ->withCount('contacts')
            ->latest()
            ->get();

        return view('contact-tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
        ]);

        WaContactTag::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        return back()->with('success', 'Tag berhasil dibuat.');
    }

    public function update(Request $request, WaContactTag $tag)
    {
        abort_if($tag->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:20',
        ]);

        $tag->update($validated);

        return back()->with('success', 'Tag diperbarui.');
    }

    public function destroy(WaContactTag $tag)
    {
        abort_if($tag->user_id !== Auth::id(), 403);

        $tag->contacts()->detach();
        $tag->delete();

        return back()->with('success', 'Tag dihapus.');
    }

    public function attach(Request $request, WaContactTag $tag)
    {
        abort_if($tag->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'contact_id' => 'required|exists:wa_contacts,id',
        ]);

        $contact = WaContact::where('user_id', Auth::id())
            ->findOrFail($validated['contact_id']);

        $tag->contacts()->syncWithoutDetaching([$contact->id]);

        return back()->with('success', 'Tag ditempelkan ke kontak.');
    }

    public function detach(Request $request, WaContactTag $tag)
    {
        abort_if($tag->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'contact_id' => 'required|exists:wa_contacts,id',
        ]);

        $contact = WaContact::where('user_id', Auth::id())
            ->findOrFail($validated['contact_id']);

        $tag->contacts()->detach($contact->id);

        return back()->with('success', 'Tag dilepaskan dari kontak.');
    }
}
