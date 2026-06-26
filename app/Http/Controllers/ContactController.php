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
            ->latest()
            ->paginate(25);

        return view('contacts.index', compact('contacts'));
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

        return back()->with('success', 'Kontak disimpan.');
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

        return back()->with('success', 'Kontak diperbarui.');
    }

    public function destroy(WaContact $contact)
    {
        abort_if($contact->user_id !== Auth::id(), 403);
        $contact->delete();

        return back()->with('success', 'Kontak dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $count = 0;

        fgetcsv($handle); // skip header

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $name = trim($row[0]);
            $phone = preg_replace('/[^0-9]/', '', trim($row[1]));
            $tags = isset($row[2]) ? array_map('trim', explode(',', $row[2])) : null;

            if (empty($name) || empty($phone)) continue;

            WaContact::updateOrCreate(
                ['user_id' => Auth::id(), 'phone' => $phone],
                ['name' => $name, 'tags' => $tags]
            );
            $count++;
        }

        fclose($handle);

        return back()->with('success', "{$count} kontak berhasil diimport.");
    }
}
