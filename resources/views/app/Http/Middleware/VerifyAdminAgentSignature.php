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
            \Log::warning('[AgentAPI] Rejected: missing signature headers.', [
                'path' => $request->path(),
                'has_timestamp' => (bool) $timestamp,
                'has_signature' => (bool) $signature,
            ]);
            abort(401, 'Missing signature headers.');
        }

        if (abs(time() - (int) $timestamp) > 300) {
            \Log::warning('[AgentAPI] Rejected: stale timestamp.', [
                'path' => $request->path(),
                'request_ts' => $timestamp,
                'server_ts' => time(),
                'skew_seconds' => time() - (int) $timestamp,
            ]);
            abort(401, 'Stale request.');
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            // Never log the secret or the working signature itself — just
            // enough to tell "wrong secret" apart from "body mismatch"
            // without leaking anything an attacker could use.
            \Log::warning('[AgentAPI] Rejected: signature mismatch.', [
                'path' => $request->path(),
                'body_length' => strlen($request->getContent()),
                'secret_configured' => true,
                'secret_length' => strlen($secret),
            ]);
            abort(401, 'Invalid signature.');
        }

        \Log::info('[AgentAPI] Signature verified OK.', ['path' => $request->path()]);

        return $next($request);
    }
}
