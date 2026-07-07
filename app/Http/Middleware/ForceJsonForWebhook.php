<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonForWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (str_starts_with($request->path(), 'webhook')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
