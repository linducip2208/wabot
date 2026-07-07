<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        return Cache::remember('sitemap.xml', now()->addHours(24), function () {
            $urls = $this->collectUrls();
            $xml = $this->renderXml($urls);

            return Response::make($xml, 200, ['Content-Type' => 'application/xml']);
        });
    }

    protected function collectUrls(): array
    {
        $baseUrl = rtrim(config('app.url'), '/');
        $urls = [];

        // Static pages
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/docs', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => '/welcome', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/blog', 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => $baseUrl . $page['loc'],
                'priority' => $page['priority'],
                'changefreq' => $page['changefreq'],
                'lastmod' => now()->toIso8601String(),
            ];
        }

        // Blog posts
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $post->slug,
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $post->updated_at->toIso8601String(),
            ];
        }

        // CMS pages
        $pages = CmsPage::where('is_published', true)->get();

        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $baseUrl . '/pages/' . $page->slug,
                'priority' => '0.7',
                'changefreq' => 'monthly',
                'lastmod' => $page->updated_at->toIso8601String(),
            ];
        }

        return $urls;
    }

    protected function renderXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
