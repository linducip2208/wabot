<?php

namespace App\Services;

use App\Models\WaClickEvent;
use App\Models\WaCampaign;
use Illuminate\Support\Str;

class ClickTrackerService
{
    /**
     * Generate tracking link untuk broadcast/campaign.
     */
    public function wrap(string $originalUrl, int $userId, ?int $campaignId = null): string
    {
        $token = Str::random(32);

        WaClickEvent::create([
            'user_id' => $userId,
            'campaign_id' => $campaignId,
            'contact_id' => 0,
            'link_url' => $originalUrl,
            'clicked_at' => now(),
        ])->update(['token' => $token]);

        return route('click.redirect', ['token' => $token]);
    }

    /**
     * Proses redirect dari tracking link.
     */
    public function redirect(string $token, ?int $contactId = null): ?string
    {
        $event = WaClickEvent::where('token', $token)->first();
        if (!$event) return null;

        $event->update([
            'contact_id' => $contactId ?? $event->contact_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'clicked_at' => now(),
        ]);

        if ($event->campaign) {
            $event->campaign->increment('total_clicks');
        }

        return $event->link_url;
    }

    public function getStats(int $userId, ?int $campaignId = null): array
    {
        $query = WaClickEvent::where('user_id', $userId)
            ->where('contact_id', '>', 0);

        if ($campaignId) $query->where('campaign_id', $campaignId);

        $total = $query->count();
        $uniqueContacts = $query->distinct('contact_id')->count('contact_id');

        return [
            'total_clicks' => $total,
            'unique_contacts' => $uniqueContacts,
        ];
    }
}
