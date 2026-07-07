<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow {--limit=100 : Max URL per run}';
    protected $description = 'Submit recent URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $indexNow): int
    {
        $urls = [];
        $baseUrl = rtrim(config('app.url'), '/');

        // Blog posts
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('updated_at')
            ->limit($this->option('limit'))
            ->get();

        foreach ($posts as $post) {
            $urls[] = $baseUrl . '/blog/' . $post->slug;
        }

        // CMS pages
        $pages = CmsPage::where('is_published', true)
            ->latest('updated_at')
            ->limit(50)
            ->get();

        foreach ($pages as $page) {
            $urls[] = $baseUrl . '/pages/' . $page->slug;
        }

        // Static pages
        $staticPages = ['/', '/docs', '/welcome'];
        foreach ($staticPages as $path) {
            $urls[] = $baseUrl . $path;
        }

        $urls = array_unique($urls);

        $this->info("Submitting " . count($urls) . " URLs to IndexNow...");

        foreach ($urls as $url) {
            $indexNow->submit($url);
            $this->line("  <fg=gray>✓</> {$url}");
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
