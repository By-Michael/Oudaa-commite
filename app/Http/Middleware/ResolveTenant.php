<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use App\Support\CurrentCommunity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on every {tenant}/... route. Looks up the slug in the tenants
 * table, then sets CurrentCommunity for the rest of this request —
 * every model using App\Models\Concerns\BelongsToCommunity picks that
 * up automatically and scopes/stamps its queries with it.
 *
 * There is no database connection swap anymore (that only made sense
 * when each tenant had its own SQLite file). One shared connection,
 * one column doing the isolation.
 *
 * Every existing controller/model in this app (ResidentController,
 * Fund, Committee auth, ...) is completely unaware this is happening;
 * they just use the model as they always did and the global scope
 * does the filtering.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenant');

        $tenant = Tenant::where('slug', $slug)->first();

        if (! $tenant) {
            abort(404);
        }

        if ($tenant->status === 'failed') {
            return response()->view('tenants.failed', ['tenant' => $tenant], 503);
        }

        app(CurrentCommunity::class)->set($tenant->id);

        // Isolate sessions per community so logging into one committee
        // panel never gets treated as a session for another's.
        Config::set('session.cookie', 'oudaa_session_'.$tenant->slug);

        // Lets every existing route('dashboard'), route('fees.index'),
        // etc. keep working unchanged even though those routes now
        // technically require a {tenant} parameter.
        URL::defaults(['tenant' => $tenant->slug]);

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
