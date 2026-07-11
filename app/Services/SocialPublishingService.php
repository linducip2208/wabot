<?php

namespace App\Services;

use App\Models\WaPost;
use App\Models\WaSocialAccount;
use App\Jobs\PublishPostJob;

class SocialPublishingService
{
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

    public function publishToPlatform(WaPost $post, string $platform, WaSocialAccount $account): array
    {
        $result = ['platform' => $platform, 'success' => false, 'message' => '', 'post_id' => null];

        try {
            switch ($platform) {
                case WaSocialAccount::PLATFORM_FACEBOOK_PAGE:
                    $result = $this->publishToFacebook($post, $account);
                    break;
                case WaSocialAccount::PLATFORM_INSTAGRAM_PROFESSIONAL:
                    $result = $this->publishToInstagram($post, $account);
                    break;
                case WaSocialAccount::PLATFORM_X_TWITTER:
                    $result = $this->publishToTwitter($post, $account);
                    break;
                case WaSocialAccount::PLATFORM_TIKTOK:
                    $result = $this->publishToTikTok($post, $account);
                    break;
                case WaSocialAccount::PLATFORM_LINKEDIN_PAGE:
                    $result = $this->publishToLinkedIn($post, $account);
                    break;
                default:
                    $result['message'] = "Unsupported platform: {$platform}";
            }
        } catch (\Throwable $e) {
            $result['message'] = $e->getMessage();
        }

        return $result;
    }

    protected function publishToFacebook(WaPost $post, WaSocialAccount $account): array
    {
        $fbService = new FacebookService();

        $responses = [];
        $hasImage = !empty($post->media_urls) && is_array($post->media_urls);

        if ($hasImage) {
            foreach ($post->media_urls as $mediaUrl) {
                $responses[] = $fbService->sendImage(
                    new \App\Models\WaFacebookAccount([
                        'page_id' => $account->platform_id,
                    ]),
                    '',
                    $mediaUrl
                );
            }
        }

        if ($post->content) {
            $responses[] = $fbService->sendMessage(
                new \App\Models\WaFacebookAccount([
                    'page_id' => $account->platform_id,
                ]),
                '',
                $post->content
            );
        }

        return [
            'platform' => 'facebook_page',
            'success' => true,
            'message' => 'Published to Facebook Page',
            'post_id' => $account->platform_id,
            'responses' => $responses,
        ];
    }

    protected function publishToInstagram(WaPost $post, WaSocialAccount $account): array
    {
        $igService = new InstagramService();

        $responses = [];
        $hasImage = !empty($post->media_urls) && is_array($post->media_urls);

        if ($hasImage) {
            foreach ($post->media_urls as $mediaUrl) {
                $responses[] = $igService->sendImage(
                    $account->platform_id,
                    $account->access_token,
                    $mediaUrl
                );
            }
        }

        if ($post->content) {
            $responses[] = $igService->sendDM(
                $account->platform_id,
                $account->access_token,
                $post->content
            );
        }

        return [
            'platform' => 'instagram_professional',
            'success' => !isset($responses[0]['error']),
            'message' => 'Published to Instagram',
            'post_id' => $account->platform_id,
            'responses' => $responses,
        ];
    }

    protected function publishToTwitter(WaPost $post, WaSocialAccount $account): array
    {
        $twitterService = new TwitterService();

        $response = $twitterService->sendTweet($account->access_token, $post->content);

        return [
            'platform' => 'x_twitter',
            'success' => ($response['ok'] ?? false),
            'message' => ($response['ok'] ?? false) ? 'Tweet published' : ($response['error'] ?? 'Failed'),
            'post_id' => $response['data']['id'] ?? null,
            'response' => $response,
        ];
    }

    protected function publishToTikTok(WaPost $post, WaSocialAccount $account): array
    {
        $tiktokService = new TikTokService();

        $response = $tiktokService->sendMessage(
            $account->access_token,
            $account->platform_id,
            $post->content
        );

        return [
            'platform' => 'tiktok',
            'success' => ($response['ok'] ?? false),
            'message' => ($response['ok'] ?? false) ? 'TikTok message sent' : ($response['error'] ?? 'Failed'),
            'response' => $response,
        ];
    }

    protected function publishToLinkedIn(WaPost $post, WaSocialAccount $account): array
    {
        return [
            'platform' => 'linkedin_page',
            'success' => false,
            'message' => 'LinkedIn publishing requires OAuth 2.0 setup. Configure your LinkedIn app credentials.',
        ];
    }
}
