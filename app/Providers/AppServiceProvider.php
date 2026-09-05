<?php

namespace App\Providers;

use App\Models\SlowQuery;
use App\Support\CurrentCommunity;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /** Anything slower than this gets persisted for the admin dashboard. */
    private const SLOW_QUERY_THRESHOLD_MS = 200;

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

        $this->recordSlowQueries();
        $this->registerBrevoMailer();
    }

    /**
     * Brevo (formerly Sendinblue) isn't one of Laravel's built-in mailer
     * drivers, so — unlike ses/postmark/resend in config/mail.php, which
     * Laravel already knows how to build — it has to be registered by
     * hand via Mail::extend(), using the transport factory that ships in
     * the symfony/sendinblue-mailer package (composer require it before
     * this will resolve). The 'default' host segment below is a Symfony
     * Mailer convention for API-based transports with no custom host —
     * it's not a placeholder to fill in.
     */
    private function registerBrevoMailer(): void
    {
        Mail::extend('brevo', function (array $config = []) {
            return (new SendinblueTransportFactory())->create(
                new Dsn('sendinblue+api', 'default', $config['key'] ?? null)
            );
        });
    }

    /**
     * Feeds the God Admin dashboard's "slow queries" panel. Fires on
     * every query, so this has to be cheap and can never itself throw —
     * a broken listener would break every DB call in the app.
     */
    private function recordSlowQueries(): void
    {
        DB::listen(function (QueryExecuted $query) {
            if ($query->time < self::SLOW_QUERY_THRESHOLD_MS) {
                return;
            }

            // Guard against logging the INSERT below as its own slow
            // query and recursing forever.
            if (str_contains($query->sql, 'slow_queries')) {
                return;
            }

            try {
                SlowQuery::create([
                    'sql' => $query->sql,
                    'bindings' => array_map(fn ($b) => is_scalar($b) || $b === null ? $b : (string) $b, $query->bindings),
                    'time_ms' => (int) round($query->time),
                    'path' => request()?->path(),
                ]);
            } catch (\Throwable $e) {
                // best-effort — never let logging break the request
            }
        });
    }
}

