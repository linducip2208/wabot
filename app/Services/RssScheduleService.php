<?php

namespace App\Services;

use App\Models\WaPost;
use App\Models\WaRssSchedule;
use App\Models\WaRssScheduleHistory;
use App\Models\WaSocialAccount;
use Illuminate\Support\Facades\Log;

class RssScheduleService
{
    public function checkFeed(WaRssSchedule $schedule): array
    {
        $result = ['new_items' => 0, 'posts_created' => 0, 'errors' => []];

        try {
            $content = @file_get_contents($schedule->feed_url);
            if ($content === false) {
                $result['errors'][] = "Failed to fetch feed: {$schedule->feed_url}";
                return $result;
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($content);
            if ($xml === false) {
                $result['errors'][] = "Failed to parse XML from: {$schedule->feed_url}";
                return $result;
            }

            $items = $xml->xpath('//item') ?: $xml->xpath('//entry');

            if (empty($items)) {
                $result['errors'][] = "No items found in feed: {$schedule->feed_url}";
                $schedule->update(['last_checked_at' => now()]);
                return $result;
            }

            foreach ($items as $item) {
                $title = (string) ($item->title ?? '');
                $link = (string) ($item->link ?? '');
                $description = (string) ($item->description ?? '');
                $pubDate = (string) ($item->pubDate ?? $item->published ?? '');

                if (empty($link) || empty($title)) {
                    continue;
                }

                $contentHash = hash('sha256', $link);

                $exists = WaRssScheduleHistory::where('rss_schedule_id', $schedule->id)
                    ->where('content_hash', $contentHash)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $result['new_items']++;

                $postContent = $title;
                if ($description) {
                    $postContent .= "\n\n" . strip_tags($description);
                }
                if ($link) {
                    $postContent .= "\n\n" . $link;
                }

                $post = WaPost::create([
                    'user_id' => $schedule->user_id,
                    'content' => $postContent,
                    'platform_targets' => $schedule->platform_targets,
                    'status' => WaPost::STATUS_SCHEDULED,
                    'scheduled_at' => now(),
                ]);

                WaRssScheduleHistory::create([
                    'rss_schedule_id' => $schedule->id,
                    'post_url' => $link,
                    'content_hash' => $contentHash,
                    'published_at' => $pubDate ? now()->parse($pubDate) : now(),
                ]);

                \App\Jobs\PublishPostJob::dispatch($post->id);
                $result['posts_created']++;
            }

            $schedule->update(['last_checked_at' => now()]);
        } catch (\Throwable $e) {
            Log::error("RSS Schedule check failed for {$schedule->name}: {$e->getMessage()}");
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    public function autoPost(WaPost $post): void
    {
        if (empty($post->platform_targets)) {
            $post->update(['status' => WaPost::STATUS_FAILED, 'result' => ['error' => 'No platform targets']]);
            return;
        }

        \App\Jobs\PublishPostJob::dispatch($post->id);
    }
}
