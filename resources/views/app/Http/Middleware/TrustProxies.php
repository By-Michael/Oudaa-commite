<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * Render (and most PaaS hosts: Railway, Fly.io, Heroku, etc.) terminate
     * TLS at their own edge load balancer and forward plain HTTP to this
     * container, adding X-Forwarded-* headers to say what the original
     * request actually looked like. Trusting '*' here is standard/safe in
     * this setup: the container isn't reachable directly from the public
     * internet, only through Render's proxy, so there's no one else who
     * could spoof these headers.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
