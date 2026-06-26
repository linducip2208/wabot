<?php

namespace App\Http\Controllers;

use App\Models\ContactGroup;
use App\Models\WaContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    public function index()
    {
        $groups = ContactGroup::where('user_id', Auth::id())->withCount('contacts')->get();
        return view('groups.index', compact('groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        ContactGroup::create([
            'user_id' => Auth::id(),
            'name' => $data['name'],
            'color' => $data['color'] ?? '#3b82f6',
        ]);

        return back()->with('success', 'Grup dibuat.');
    }

    public function update(Request $request, ContactGroup $group)
    {
        abort_if($group->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
        ]);

        $group->update($data);
        return back()->with('success', 'Grup diperbarui.');
    }

    public function destroy(ContactGroup $group)
    {
        abort_if($group->user_id !== Auth::id(), 403);
        $group->delete();
        return back()->with('success', 'Grup dihapus.');
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'contact_ids' => 'required|array',
            'contact_ids.*' => 'exists:wa_contacts,id',
            'group_id' => 'required|exists:contact_groups,id',
        ]);

        $group = ContactGroup::where('user_id', Auth::id())->findOrFail($data['group_id']);
        $group->contacts()->syncWithoutDetaching($data['contact_ids']);

        return back()->with('success', count($data['contact_ids']) . ' kontak ditambahkan ke grup.');
    }
}
