<?php

namespace App\Jobs;

use App\Models\WaPost;
use App\Models\WaSocialAccount;
use App\Services\SocialPublishingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $postId;
    public int $tries = 3;

    public function __construct(int $postId)
    {
        $this->postId = $postId;
    }

    public function handle(SocialPublishingService $service): void
    {
        $post = WaPost::find($this->postId);

        if (!$post) {
            Log::warning("PublishPostJob: Post #{$this->postId} not found.");
            return;
        }

        if ($post->isPublished()) {
            return;
        }

        $platforms = $post->platform_targets ?? [];
        if (empty($platforms)) {
            $post->update([
                'status' => WaPost::STATUS_FAILED,
                'result' => ['error' => 'No platform targets configured'],
            ]);
            return;
        }

        $results = [];
        $allSuccess = true;

        foreach ($platforms as $platform) {
            $account = WaSocialAccount::where('user_id', $post->user_id)
                ->where('platform', $platform)
                ->where('is_active', true)
                ->first();

            if (!$account) {
                $results[] = [
                    'platform' => $platform,
                    'success' => false,
                    'message' => "No active account found for {$platform}",
                ];
                $allSuccess = false;
                continue;
            }

            $result = $service->publishToPlatform($post, $platform, $account);
            $results[] = $result;

            if (!$result['success']) {
                $allSuccess = false;
            }
        }

        $post->update([
            'status' => $allSuccess ? WaPost::STATUS_PUBLISHED : WaPost::STATUS_FAILED,
            'published_at' => now(),
            'result' => $results,
        ]);

        Log::info("PublishPostJob: Post #{$this->postId} " . ($allSuccess ? 'published' : 'failed') . " to " . count($platforms) . " platforms.");
    }
}
