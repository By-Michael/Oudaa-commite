<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * Mint a one-time token the admin's browser will redeem at
     * /admin-bridge/{token} to log in as the target user. Never
     * exposes the session directly to the admin app.
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

        $token = Str::random(64);

        DB::table('admin_impersonation_tokens')->insert([
            'token' => $token,
            'committee_id' => $user->id,
            'expires_at' => now()->addSeconds(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \Log::channel('single')->warning('[GOD-ADMIN] Impersonation token issued', [
            'committee_id' => $user->id,
            'tenant' => $tenant->slug,
            'reason' => $request->input('reason'),
            'requested_from_ip' => $request->input('requested_by_ip'),
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
