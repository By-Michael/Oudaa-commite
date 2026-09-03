<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The public landing site is English-only. This runs after SetLocale
 * (which applies the session-stored locale, shared with the tenant
 * committee panel) and overrides it back to English for landing
 * requests, without touching the session itself — so a committee
 * member's Amharic preference for their tenant panel is unaffected
 * when they browse the public site.
 */
class ForceLandingLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('en');
        view()->share('currentLocale', 'en');

        return $next($request);
    }
}
