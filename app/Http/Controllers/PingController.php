<?php

namespace App\Http\Controllers;

class PingController extends Controller
{
    /**
     * Hit silently in the background by the front-end to keep the
     * session alive while the tab is open (see layouts/app.blade.php).
     *
     * This used to be a Closure route. Route closures cannot be
     * serialized by `php artisan route:cache`, so on any deploy that
     * runs route:cache (see docker/start.sh) the cache step threw and,
     * because start.sh runs under `set -e`, killed the container's
     * startup before the app ever came up — every route, not just this
     * one, would 404/fail to respond. Keeping this as a real class
     * avoids that failure mode entirely.
     */
    public function __invoke()
    {
        return response()->noContent();
    }
}
