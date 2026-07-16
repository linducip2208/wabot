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
    public function wrap(string $originalUrl, int $userId, ?int $campaignId = null, ?int $contactId = null): string
    {
        $token = Str::random(32);

        WaClickEvent::create([
            'user_id' => $userId,
            'campaign_id' => $campaignId,
            'contact_id' => $contactId,
            'token' => $token,
            'link_url' => $originalUrl,
            'clicked_at' => null,
        ]);

        return route('click.redirect', ['token' => $token]);
    }

    /**
     * Rewrite semua URL di dalam pesan menjadi tracking link.
     */
    public function wrapMessage(string $message, int $userId, ?int $campaignId = null, ?int $contactId = null): string
    {
        return preg_replace_callback(
            '/https?:\/\/[^\s<>"]+/i',
            function ($m) use ($userId, $campaignId, $contactId) {
                if (str_contains($m[0], '/click/')) {
                    return $m[0];
                }
                return $this->wrap($m[0], $userId, $campaignId, $contactId);
            },
            $message
        );
    }

    /**
     * Proses redirect dari tracking link.
     */
    public function redirect(string $token, ?int $contactId = null): ?string
    {
        $event = WaClickEvent::where('token', $token)->first();
        if (!$event) return null;

        $firstClick = $event->clicked_at === null;

        $event->update([
            'contact_id' => $contactId ?? $event->contact_id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'clicked_at' => now(),
        ]);

        if ($firstClick && $event->campaign) {
            $event->campaign->increment('total_clicks');
        }

        return $event->link_url;
    }

    public function getStats(int $userId, ?int $campaignId = null): array
    {
        $query = WaClickEvent::where('user_id', $userId)
            ->whereNotNull('clicked_at');

        if ($campaignId) $query->where('campaign_id', $campaignId);

        $total = $query->count();
        $uniqueContacts = $query->clone()->whereNotNull('contact_id')->distinct('contact_id')->count('contact_id');

        return [
            'total_clicks' => $total,
            'unique_contacts' => $uniqueContacts,
        ];
    }
}
