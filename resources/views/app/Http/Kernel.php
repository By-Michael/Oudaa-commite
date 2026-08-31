<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // Feeds the God Admin performance dashboard (response time, error
        // rate). Cheap (one INSERT) and fails silently if it can't write.
        \App\Http\Middleware\RecordSystemMetrics::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        // Used for every /{tenant}/... route instead of 'web'.
        // ResolveTenant MUST run before StartSession: it swaps the
        // session cookie name to one scoped to this tenant, and that
        // has to happen before the session middleware decides which
        // cookie to read. Putting them in one explicit group avoids
        // depending on Laravel's middleware priority sorting to get
        // that order right.
        'tenant-web' => [
            \App\Http\Middleware\ResolveTenant::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\CommitteeAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'tenant' => \App\Http\Middleware\ResolveTenant::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}
