<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Verifies requests coming from the separate God Admin dashboard.
 * The admin app signs every request with HMAC-SHA256 over
 * "{timestamp}.{raw_body}" using a shared secret (ADMIN_AGENT_SECRET),
 * which must match the "Agent API secret" entered for this instance in
 * the admin app's Settings > Instances page. Timestamps older than 5
 * minutes are rejected to limit replay windows.
 */
class VerifyAdminAgentSignature
{
    public function handle($request, Closure $next)
    {
        $secret = config('services.admin_agent.secret');

        if (! $secret) {
            abort(503, 'Agent API is not configured on this instance.');
        }

        $timestamp = $request->header('X-Admin-Timestamp');
        $signature = $request->header('X-Admin-Signature');

        if (! $timestamp || ! $signature) {
            abort(401, 'Missing signature headers.');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            abort(401, 'Stale request.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid signature.');
        }

        return $next($request);
    }
}
