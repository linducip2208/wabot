<?php

namespace App\Services;

use App\Models\WaFacebookAccount;
use App\Models\WaInstagramAccount;
use App\Models\WaPost;
use App\Models\WaSocialAccount;
use App\Models\WaTiktokAccount;
use App\Models\WaTwitterAccount;
use App\Jobs\PublishPostJob;

class SocialPublishingService
{
    public function __construct(
        protected FacebookService $facebookService,
        protected InstagramService $instagramService,
        protected TwitterService $twitterService,
        protected TikTokService $tiktokService,
    ) {}

    public function createPost(array $data, int $userId): WaPost
    {
        return WaPost::create([
            'user_id' => $userId,
            'content' => $data['content'] ?? null,
            'media_urls' => $data['media_urls'] ?? null,
            'platform_targets' => $data['platform_targets'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => $data['scheduled_at'] ? WaPost::STATUS_SCHEDULED : WaPost::STATUS_DRAFT,
            'campaign_id' => $data['campaign_id'] ?? null,
            'label_id' => $data['label_id'] ?? null,
        ]);
    }

    public function publishNow(WaPost $post): WaPost
    {
        $post->update([
            'status' => WaPost::STATUS_SCHEDULED,
            'scheduled_at' => now(),
        ]);

        PublishPostJob::dispatch($post->id);

        return $post->fresh();
    }

    public function publishScheduled(): int
    {
        $posts = WaPost::due()->get();
        $count = 0;

        foreach ($posts as $post) {
            PublishPostJob::dispatch($post->id);
            $count++;
        }

        return $count;
    }

    public function publishToFacebook(WaPost $post, WaFacebookAccount $account): array
    {
        $responses = [];
        $hasImage = !empty($post->media_urls) && is_array($post->media_urls);

        if ($hasImage) {
            foreach ($post->media_urls as $mediaUrl) {
                $responses[] = $this->facebookService->sendImage($account, '', $mediaUrl);
            }
        }

        if ($post->content) {
            $responses[] = $this->facebookService->sendMessage($account, '', $post->content);
        }

        return [
            'platform' => 'facebook_page',
            'success' => true,
            'message' => 'Published to Facebook Page',
            'post_id' => $account->page_id,
            'responses' => $responses,
        ];
    }

    public function publishToInstagram(WaPost $post, WaInstagramAccount $account): array
    {
        $responses = [];
        $hasImage = !empty($post->media_urls) && is_array($post->media_urls);

        if ($hasImage) {
            foreach ($post->media_urls as $mediaUrl) {
                $responses[] = $this->instagramService->sendImage(
                    $account->instagram_id,
                    $account->access_token,
                    $mediaUrl
                );
            }
        }

        return [
            'platform' => 'instagram_professional',
            'success' => !isset($responses[0]['error']),
            'message' => 'Published to Instagram',
            'post_id' => $account->instagram_id,
            'responses' => $responses,
        ];
    }

    public function publishToTwitter(WaPost $post, WaTwitterAccount $account): array
    {
        $response = $this->twitterService->sendTweet($account->access_token, $post->content);

        return [
            'platform' => 'x_twitter',
            'success' => ($response['ok'] ?? false),
            'message' => ($response['ok'] ?? false) ? 'Tweet published' : ($response['error'] ?? 'Failed'),
            'post_id' => $response['data']['id'] ?? null,
            'response' => $response,
        ];
    }

    public function publishToTikTok(WaPost $post, WaTiktokAccount $account): array
    {
        $response = $this->tiktokService->sendMessage(
            $account->access_token,
            $account->open_id ?? '',
            $post->content
        );

        return [
            'platform' => 'tiktok',
            'success' => ($response['ok'] ?? false),
            'message' => ($response['ok'] ?? false) ? 'TikTok message sent' : ($response['error'] ?? 'Failed'),
            'response' => $response,
        ];
    }
}
