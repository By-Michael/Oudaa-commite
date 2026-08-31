<?php

namespace App\Providers;

use App\Support\CurrentCommunity;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(CurrentCommunity::class);
    }

    public function boot()
    {
        // The framework's built-in pagination views depend on Tailwind's
        // responsive utility classes (sm:hidden, hidden sm:flex, ...) to
        // toggle between a mobile and a desktop layout. This app doesn't
        // load Tailwind, so both layouts rendered at once and the SVG
        // chevron icons (meant to be sized by Tailwind's h-5/w-5) showed
        // up huge and unstyled. Use a plain view built for this app's own
        // CSS instead.
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');

        // Belt-and-braces on top of TrustProxies: every url()/route() call
        // (bridge URLs, signed callbacks, etc.) must generate https://,
        // never http://, or an outbound call from the admin app can get
        // silently 301-downgraded from POST to GET at Render's edge.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
