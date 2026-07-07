<?php

namespace App\Http\Controllers;

use App\Models\WaTeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TeamMemberController extends Controller
{
    public function index()
    {
        $members = WaTeamMember::where('user_id', Auth::id())
            ->withCount('activeConversations')
            ->latest()
            ->get();

        return view('team.index', compact('members'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:wa_team_members,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string|max:50',
            'max_concurrent' => 'required|integer|min:1|max:100',
        ]);

        WaTeamMember::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'max_concurrent' => $validated['max_concurrent'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Anggota tim berhasil ditambahkan.');
    }

    public function update(Request $request, WaTeamMember $member)
    {
        abort_if($member->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:wa_team_members,email,' . $member->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|string|max:50',
            'max_concurrent' => 'required|integer|min:1|max:100',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'max_concurrent' => $validated['max_concurrent'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $member->update($data);

        return back()->with('success', 'Anggota tim berhasil diperbarui.');
    }

    public function destroy(WaTeamMember $member)
    {
        abort_if($member->user_id !== Auth::id(), 403);

        $member->assignments()->where('status', 'active')->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        $member->delete();

        return back()->with('success', 'Anggota tim dihapus dan semua penugasan aktif ditutup.');
    }
}
