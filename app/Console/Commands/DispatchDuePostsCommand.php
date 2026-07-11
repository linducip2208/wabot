<?php

namespace App\Console\Commands;

use App\Services\SocialPublishingService;
use Illuminate\Console\Command;

class DispatchDuePostsCommand extends Command
{
    protected $signature = 'wabot:dispatch-posts';
    protected $description = 'Dispatch due scheduled posts for publishing';

    public function handle(SocialPublishingService $service): void
    {
        $count = $service->publishScheduled();
        $this->info("Dispatched {$count} due posts.");
    }
}
