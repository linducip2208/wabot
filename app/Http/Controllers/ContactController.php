<?php

namespace App\Http\Controllers;

use App\Models\WaContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = WaContact::where('user_id', Auth::id())
            ->with('groups')
            ->latest()
            ->paginate(25);

        $groups = \App\Models\ContactGroup::where('user_id', Auth::id())
            ->orderBy('name')->get();

        return view('contacts.index', compact('contacts', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'tags' => 'nullable|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['phone']);

        WaContact::updateOrCreate(
            ['user_id' => Auth::id(), 'phone' => $phone],
            [
                'name' => $validated['name'],
                'tags' => $validated['tags'] ? explode(',', $validated['tags']) : null,
            ]
        );

        return back()->with('success', __('messages.success.contact_saved'));
    }

    public function update(Request $request, WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'tags' => 'nullable|string',
        ]);

        $contact->update([
            'name' => $validated['name'],
            'phone' => preg_replace('/[^0-9]/', '', $validated['phone']),
            'tags' => $validated['tags'] ? explode(',', $validated['tags']) : null,
        ]);

        return back()->with('success', __('messages.success.contact_updated'));
    }

    public function destroy(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);
        $contact->delete();

        return back()->with('success', __('messages.success.contact_deleted'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx|max:10240',
            'group_id' => 'nullable|exists:contact_groups,id',
            'new_group_name' => 'nullable|string|max:255',
        ]);

        $group = null;
        if ($request->filled('new_group_name')) {
            $group = \App\Models\ContactGroup::firstOrCreate(
                ['user_id' => Auth::id(), 'name' => trim($request->input('new_group_name'))],
                ['color' => '#3b82f6']
            );
        } elseif ($request->filled('group_id')) {
            $group = \App\Models\ContactGroup::where('user_id', Auth::id())
                ->findOrFail($request->input('group_id'));
        }

        $rows = app(\App\Services\SpreadsheetImportService::class)->parse($request->file('file'));
        $count = 0;
        $contactIds = [];

        foreach ($rows as $index => $row) {
            if (empty($row) || count(array_filter($row)) === 0) continue;

            $name = trim((string) ($row[0] ?? ''));
            $rawPhone = trim((string) ($row[1] ?? ''));

            if ($index === 0 && !preg_match('/[0-9]{6,}/', $name . $rawPhone)) continue; // skip header

            $phone = preg_replace('/[^0-9]/', '', $rawPhone);
            $tags = isset($row[2]) && trim((string) $row[2]) !== ''
                ? array_map('trim', explode(',', (string) $row[2]))
                : null;

            if (empty($name) || strlen($phone) < 6) continue;

            $contact = WaContact::updateOrCreate(
                ['user_id' => Auth::id(), 'phone' => $phone],
                ['name' => $name, 'tags' => $tags]
            );
            $contactIds[] = $contact->id;
            $count++;
        }

        if ($group && !empty($contactIds)) {
            $group->contacts()->syncWithoutDetaching($contactIds);
        }

        return back()->with('success', __('messages.success.contact_imported', ['count' => $count]));
    }
}
