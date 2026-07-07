<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    protected string $keyFile;
    protected string $key;

    protected array $endpoints = [
        'https://api.indexnow.org/indexnow',
        'https://www.bing.com/indexnow',
        'https://yandex.com/indexnow',
        'https://search.seznam.cz/indexnow',
        'https://searchadvisor.naver.com/indexnow',
    ];

    public function __construct()
    {
        $this->keyFile = public_path('indexnow-key.txt');
        $this->key = $this->loadOrGenerateKey();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function submit(string $url): bool
    {
        $cacheKey = 'indexnow:submitted:' . md5($url);

        if (Cache::has($cacheKey)) {
            return true;
        }

        $siteUrl = config('app.url');

        foreach ($this->endpoints as $endpoint) {
            try {
                $response = Http::timeout(15)->post($endpoint, [
                    'host' => parse_url($siteUrl, PHP_URL_HOST),
                    'key' => $this->key,
                    'keyLocation' => $siteUrl . '/indexnow-key.txt',
                    'urlList' => [$url],
                ]);

                if ($response->successful()) {
                    Log::info("IndexNow: submitted {$url} to {$endpoint}");
                }
            } catch (\Throwable $e) {
                Log::warning("IndexNow: failed {$endpoint} — {$e->getMessage()}");
            }
        }

        Cache::put($cacheKey, true, now()->addDays(30));

        return true;
    }

    public function submitBatch(array $urls): void
    {
        foreach ($urls as $url) {
            $this->submit($url);
        }
    }

    protected function loadOrGenerateKey(): string
    {
        if (file_exists($this->keyFile)) {
            return trim(file_get_contents($this->keyFile));
        }

        $key = bin2hex(random_bytes(32));
        file_put_contents($this->keyFile, $key);

        return $key;
    }
}
