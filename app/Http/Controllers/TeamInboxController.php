<?php

namespace App\Http\Controllers;

use App\Models\WaConversationAssignment;
use App\Models\WaTeamMember;
use App\Models\WaContact;
use App\Services\TeamInboxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamInboxController extends Controller
{
    public function index()
    {
        $assignments = WaConversationAssignment::where('status', 'active')
            ->with(['contact', 'teamMember', 'session'])
            ->whereHas('contact', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->latest('assigned_at')
            ->get();

        $members = WaTeamMember::where('user_id', Auth::id())
            ->where('is_active', true)
            ->get();

        return view('team.inbox', compact('assignments', 'members'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'contact_id' => 'required|integer|exists:wa_contacts,id',
            'team_member_id' => 'required|integer|exists:wa_team_members,id',
            'session_id' => 'nullable|integer|exists:wa_sessions,id',
        ]);

        $contact = WaContact::findOrFail($validated['contact_id']);
        abort_if($contact->user_id !== Auth::id(), 403);

        $existing = WaConversationAssignment::where('contact_id', $validated['contact_id'])
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('error', __('messages.error.contact_already_assigned'));
        }

        WaConversationAssignment::create([
            'contact_id' => $validated['contact_id'],
            'team_member_id' => $validated['team_member_id'],
            'session_id' => $validated['session_id'] ?? null,
            'assigned_at' => now(),
            'status' => 'active',
        ]);

        return back()->with('success', __('messages.success.assignment_created'));
    }

    public function reassign(Request $request, WaConversationAssignment $assignment)
    {
        $contact = $assignment->contact;
        abort_if($contact->user_id !== Auth::id(), 403);

        $validated = $request->validate([
            'team_member_id' => 'required|integer|exists:wa_team_members,id',
        ]);

        $service = app(TeamInboxService::class);
        $service->reassign($assignment->contact_id, $validated['team_member_id']);

        return back()->with('success', __('messages.success.assignment_reassigned'));
    }

    public function close(WaConversationAssignment $assignment)
    {
        $contact = $assignment->contact;
        abort_if($contact->user_id !== Auth::id(), 403);

        $assignment->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', __('messages.success.assignment_closed'));
    }

    public function stats()
    {
        $members = WaTeamMember::where('user_id', Auth::id())->get();
        $service = app(TeamInboxService::class);

        $agentStats = [];
        foreach ($members as $member) {
            $agentStats[] = $service->getAgentStats($member->id);
        }

        return response()->json($agentStats);
    }
}
