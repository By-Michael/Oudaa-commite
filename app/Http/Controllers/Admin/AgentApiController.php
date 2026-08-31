<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminConsentRequest;
use App\Models\Committee;
use App\Models\SystemMetric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgentApiController extends Controller
{
    public function health()
    {
        $dbStart = microtime(true);
        try {
            DB::select('select 1');
            $dbPing = (int) ((microtime(true) - $dbStart) * 1000);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'down', 'error' => 'database unreachable']);
        }

        $window = now()->subMinutes(5);
        $recent = SystemMetric::where('created_at', '>=', $window);

        $avg = (clone $recent)->avg('duration_ms');
        $requestCount = (clone $recent)->count();
        $errorCount = (clone $recent)->where('status_code', '>=', 500)->count();

        // p95 via a simple percentile query (fine at this volume; swap for
        // a proper TDigest/roll-up table if request volume gets large).
        $durations = (clone $recent)->orderBy('duration_ms')->pluck('duration_ms');
        $p95 = $durations->isNotEmpty()
            ? $durations[(int) floor(0.95 * ($durations->count() - 1))]
            : null;

        $diskFree = @disk_free_space(base_path());
        $diskTotal = @disk_total_space(base_path());
        $diskUsedPct = ($diskFree && $diskTotal) ? round((1 - $diskFree / $diskTotal) * 100) : null;

        $queuePending = null;
        try {
            $queuePending = DB::table('jobs')->count();
        } catch (\Throwable $e) {
        }

        $errorRate = $requestCount > 0 ? round(($errorCount / $requestCount) * 100, 2) : 0;

        return response()->json([
            'status' => $errorRate > 10 ? 'degraded' : 'ok',
            'avg_response_ms' => $avg ? (int) round($avg) : null,
            'p95_response_ms' => $p95,
            'requests_per_min' => $requestCount > 0 ? (int) round($requestCount / 5) : 0,
            'error_rate' => $errorRate,
            'db_ping_ms' => $dbPing,
            'queue_pending' => $queuePending,
            'disk_used_pct' => $diskUsedPct,
        ]);
    }

    public function recentErrors(Request $request)
    {
        $limit = min((int) $request->query('limit', 50), 200);
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return response()->json(['entries' => []]);
        }

        // Tail the log file efficiently without loading it all into memory.
        $lines = $this->tailFile($logPath, 4000);
        $entries = [];

        foreach (array_reverse($lines) as $line) {
            if (count($entries) >= $limit) break;
            if (preg_match('/^\[(?<ts>[\d\-: ]+)\].*?\.(ERROR|CRITICAL|EMERGENCY|ALERT):\s*(?<msg>.*)/', $line, $m)) {
                $entries[] = ['timestamp' => $m['ts'], 'message' => Str::limit($m['msg'], 500)];
            }
        }

        return response()->json(['entries' => $entries]);
    }

    public function performanceSeries(Request $request)
    {
        $range = $request->query('range', '1h');
        $since = match ($range) {
            '24h' => now()->subDay(),
            '7d' => now()->subWeek(),
            default => now()->subHour(),
        };

        $rows = SystemMetric::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as bucket, AVG(duration_ms) as avg_ms, COUNT(*) as total, SUM(status_code >= 500) as errors")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        return response()->json(['series' => $rows]);
    }

    /**
     * Record a pending "may the admin dashboard act as you?" request and
     * make it visible to the target committee member next time they load
     * any page (see the banner partial in layouts/app.blade.php). This
     * never grants access — it only creates something a human can accept
     * or deny.
     */
    public function requestConsent(Request $request)
    {
        try {
            $request->validate([
                'tenant_slug' => 'required|string',
                'user_id' => 'required|integer',
                'consent_token' => 'required|string|max:64',
                'reason' => 'required|string',
                'callback_url' => 'required|url',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[GOD-ADMIN] Consent request rejected: invalid payload.', [
                'errors' => $e->errors(),
            ]);

            return response()->json(['ok' => false, 'error' => 'invalid_payload', 'errors' => $e->errors()], 422);
        }

        $tenant = DB::table('tenants')->where('slug', $request->input('tenant_slug'))->first();
        if (! $tenant) {
            \Log::warning('[GOD-ADMIN] Consent request rejected: unknown tenant.', [
                'tenant_slug' => $request->input('tenant_slug'),
            ]);

            return response()->json(['ok' => false, 'error' => 'tenant_not_found'], 404);
        }

        $user = Committee::where('community_id', $tenant->id)->find($request->input('user_id'));
        if (! $user) {
            \Log::warning('[GOD-ADMIN] Consent request rejected: unknown committee member for tenant.', [
                'tenant' => $tenant->slug,
                'user_id' => $request->input('user_id'),
            ]);

            return response()->json(['ok' => false, 'error' => 'user_not_found'], 404);
        }

        try {
            AdminConsentRequest::updateOrCreate(
                ['token' => $request->input('consent_token')],
                [
                    'committee_id' => $user->id,
                    'tenant_slug' => $tenant->slug,
                    'reason' => $request->input('reason'),
                    'callback_url' => $request->input('callback_url'),
                    'status' => 'pending',
                    'expires_at' => now()->addMinutes(15),
                ]
            );
        } catch (\Throwable $e) {
            // This is the failure mode that matters most: if the write
            // fails here, the admin app must NOT show a "request sent"
            // state, because no row exists for the committee member to
            // act on. Surfacing a 500 (instead of swallowing it) lets
            // AgentApiClient on the admin side know the prompt was never
            // actually created.
            \Log::error('[GOD-ADMIN] Failed to persist consent request.', [
                'tenant' => $tenant->slug,
                'committee_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['ok' => false, 'error' => 'storage_failed'], 500);
        }

        \Log::info('[GOD-ADMIN] Consent request received', [
            'committee_id' => $user->id,
            'tenant' => $tenant->slug,
            'reason' => $request->input('reason'),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Mint a one-time token the admin's browser will redeem at
     * /admin-bridge/{token} to log in as the target user. Never
     * exposes the session directly to the admin app.
     *
     * Gated on an *approved* consent request: this endpoint being
     * signature-verified only proves the caller is the admin dashboard,
     * not that the human being impersonated agreed to it. That's a
     * separate check, on purpose.
     */
    public function issueImpersonation(Request $request)
    {
        $request->validate([
            'tenant_slug' => 'required|string',
            'user_id' => 'required|integer',
            'reason' => 'required|string',
        ]);

        $tenant = DB::table('tenants')->where('slug', $request->input('tenant_slug'))->first();
        abort_unless($tenant, 404);

        $user = Committee::where('community_id', $tenant->id)->find($request->input('user_id'));
        abort_unless($user, 404);

        $consent = AdminConsentRequest::where('tenant_slug', $tenant->slug)
            ->where('committee_id', $user->id)
            ->where('status', 'approved')
            ->where('expires_at', '>', now())
            ->latest('responded_at')
            ->first();

        if (! $consent) {
            abort(403, 'No approved consent on file for this user. Ask them to accept the request first.');
        }

        $token = Str::random(64);

        DB::table('admin_impersonation_tokens')->insert([
            'token' => $token,
            'committee_id' => $user->id,
            'expires_at' => now()->addSeconds(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Single-use both ways: a redeemed bridge token can't be re-minted
        // from the same consent without the user approving again.
        $consent->update(['status' => 'expired']);

        \Log::warning('[GOD-ADMIN] Impersonation token issued', [
            'committee_id' => $user->id,
            'tenant' => $tenant->slug,
            'reason' => $request->input('reason'),
            'requested_from_ip' => $request->input('requested_by_ip'),
            'consent_id' => $consent->id,
        ]);

        return response()->json([
            'bridge_url' => url("/admin-bridge/{$token}"),
        ]);
    }

    private function tailFile(string $path, int $maxLines): array
    {
        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $start = max(0, $totalLines - $maxLines);
        $file->seek($start);

        $lines = [];
        while (! $file->eof()) {
            $lines[] = $file->fgets();
        }

        return $lines;
    }
}
