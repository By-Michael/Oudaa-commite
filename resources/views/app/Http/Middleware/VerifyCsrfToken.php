<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    // These are stateless, HMAC-signed server-to-server calls from the
    // God Admin dashboard (see VerifyAdminAgentSignature) — no session,
    // no CSRF token exists to send. The signature check is what
    // authenticates these instead.
    protected $except = [
        'internal-admin-api/*',
    ];
}
