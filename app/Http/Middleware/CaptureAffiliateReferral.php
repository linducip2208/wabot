<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\AffiliateService;
use Illuminate\Http\Request;

class CaptureAffiliateReferral
{
    public function handle(Request $request, Closure $next)
    {
        $ref = $request->query('ref');

        if ($ref && !$request->hasCookie('affiliate_ref')) {
            $cookie = cookie('affiliate_ref', $ref, 60 * 24 * 30);
            return $next($request)->withCookie($cookie);
        }

        return $next($request);
    }
}
