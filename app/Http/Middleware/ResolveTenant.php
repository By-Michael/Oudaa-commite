<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs on every {tenant}/... route. Looks up the slug in the central
 * registry, then points the 'tenant' database connection at that
 * community's own SQLite file for the rest of this request only —
 * nothing here persists between requests, so there's no risk of one
 * tenant's request bleeding into another's.
 *
 * Every existing controller/model in this app (ResidentController,
 * Fund, Committee auth, ...) is completely unaware this is happening;
 * they just use the default connection like they always did.
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

        if ($tenant->status === 'provisioning') {
            return response()->view('tenants.provisioning', ['tenant' => $tenant], 503);
        }

        if ($tenant->status === 'failed') {
            return response()->view('tenants.failed', ['tenant' => $tenant], 503);
        }

        // Point the shared 'tenant' connection at this community's file
        // and force a fresh handle — critical on long-lived workers
        // (queue, octane) where a stale connection to a *different*
        // tenant's file could otherwise still be cached.
        Config::set('database.connections.tenant.database', $tenant->db_path);
        DB::purge('tenant');
        DB::reconnect('tenant');

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
