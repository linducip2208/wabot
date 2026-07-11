<?php

namespace App\Console\Commands;

use App\Models\WaRssSchedule;
use App\Services\RssScheduleService;
use Illuminate\Console\Command;

class DispatchRssSchedulesCommand extends Command
{
    protected $signature = 'wabot:rss-schedule';
    protected $description = 'Check active RSS feeds and auto-post new items';

    public function handle(RssScheduleService $service): void
    {
        $schedules = WaRssSchedule::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('last_checked_at')
                    ->orWhereRaw("TIMESTAMPDIFF(MINUTE, last_checked_at, NOW()) >= interval_minutes");
            })
            ->get();

        $totalNew = 0;

        foreach ($schedules as $schedule) {
            $this->info("Checking: {$schedule->name} ({$schedule->feed_url})");
            $result = $service->checkFeed($schedule);

            if ($result['new_items'] > 0) {
                $this->info("  Found {$result['new_items']} new items, created {$result['posts_created']} posts.");
                $totalNew += $result['posts_created'];
            } else {
                $this->line("  No new items.");
            }

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    $this->warn("  Error: {$error}");
                }
            }
        }

        $this->info("Done. Total new posts: {$totalNew}");
    }
}
