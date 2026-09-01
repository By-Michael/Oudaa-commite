<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the user's chosen language (session-stored) to every request.
 *
 * The toggle in the landing layout and the toggle in the community
 * (tenant) app layout both write to the same 'locale' session key via
 * the /lang/{locale} routes, so a choice made on one side is remembered
 * even if the person later crosses over to the other.
 */
class SetLocale
{
    public const SUPPORTED = ['en', 'am'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (! in_array($locale, self::SUPPORTED, true)) {
            $locale = config('app.locale');
        }

        App::setLocale($locale);

        // Amharic script isn't RTL, but views can still use this to add
        // language-specific font stacks / letter-spacing tweaks.
        view()->share('currentLocale', $locale);

        return $next($request);
    }
}
