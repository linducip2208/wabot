<?php

namespace App\Http\Middleware;

use App\Services\LicenseClient;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Block ALL routes (admin, user, storefront, anything) until the app is paired
 * to a license. Only `/__pair*` and a small dev-allowlist are accessible
 * without a valid .license.lock for the current host.
 */
class RequirePair
{
    public function __construct(private LicenseClient $client) {}

    public function handle(Request $request, Closure $next): Response
    {
        Log::info('RequirePair:handle', ['path' => $request->path(), 'host' => $request->getHost()]);
        if ($this->shouldBypass($request)) {
            Log::info('RequirePair: BYPASSED');
            return $next($request);
        }

        $domain = strtolower($request->getHost());

        // DEV: always bypass on .test / localhost
        if ($this->isDevHost($domain)) {
            Log::info('RequirePair: DEV BYPASS');
            return $next($request);
        }

        $data   = $this->client->verify($domain);

        if ($data) {
            $request->attributes->set('license', $data);
            return $next($request);
        }

        // Not paired (or invalid/tampered) — redirect to wizard
        Log::info('RequirePair: REDIRECT to __pair');
        return redirect()->to('/__pair');
    }

    private function shouldBypass(Request $request): bool
    {
        $path = '/' . ltrim($request->path(), '/');

        // Always allow the wizard itself
        if (str_starts_with($path, '/__pair')) return true;

        // Health check / debug
        if ($path === '/up') return true;
        if (str_starts_with($path, '/_debugbar')) return true;

        // Public webhook receiver — harus bisa diakses tanpa license
        if (str_starts_with($path, '/webhook')) return true;

        // Public CMS pages
        if (str_starts_with($path, '/pages')) return true;

        // Localhost dev bypass
        if (config('license.dev_bypass') && app()->environment('local')) {
            $host = $request->getHost();
            if ($this->isDevHost($host)) return true;
        }

        return false;
    }

    private function isDevHost(string $host): bool
    {
        return $host === 'localhost'
            || $host === '127.0.0.1'
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.localhost');
    }
}
