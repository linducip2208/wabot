<?php

namespace App\Services;

use App\Models\WaConversationAssignment;
use App\Models\WaTeamMember;
use App\Models\WaContact;

class TeamInboxService
{
    /**
     * Auto-assign contact ke agent yang tersedia (round-robin + capacity check).
     */
    public function autoAssign(int $contactId, int $sessionId): ?WaConversationAssignment
    {
        // Cek apakah sudah assigned
        $existing = WaConversationAssignment::where('contact_id', $contactId)
            ->where('status', 'active')
            ->first();
        if ($existing) return $existing;

        // Cari agent paling sedikit beban
        $agent = WaTeamMember::where('is_active', true)
            ->withCount(['activeConversations'])
            ->get()
            ->filter(fn($a) => $a->canTakeMore())
            ->sortBy('active_conversations_count')
            ->first();

        if (!$agent) return null;

        return WaConversationAssignment::create([
            'contact_id' => $contactId,
            'team_member_id' => $agent->id,
            'session_id' => $sessionId,
            'assigned_at' => now(),
            'status' => 'active',
        ]);
    }

    public function closeAssignment(int $contactId): void
    {
        WaConversationAssignment::where('contact_id', $contactId)
            ->where('status', 'active')
            ->update(['status' => 'closed', 'closed_at' => now()]);
    }

    public function reassign(int $contactId, int $newMemberId): ?WaConversationAssignment
    {
        $this->closeAssignment($contactId);

        return WaConversationAssignment::create([
            'contact_id' => $contactId,
            'team_member_id' => $newMemberId,
            'assigned_at' => now(),
            'status' => 'active',
        ]);
    }

    public function getAgentStats(int $memberId): array
    {
        $member = WaTeamMember::findOrFail($memberId);
        return [
            'name' => $member->name,
            'active' => $member->activeConversations()->count(),
            'max' => $member->max_concurrent,
            'total_today' => WaConversationAssignment::where('team_member_id', $memberId)
                ->whereDate('assigned_at', today())->count(),
            'resolved_today' => WaConversationAssignment::where('team_member_id', $memberId)
                ->whereDate('closed_at', today())->where('status', 'closed')->count(),
        ];
    }
}
