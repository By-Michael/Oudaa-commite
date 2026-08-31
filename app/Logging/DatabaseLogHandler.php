<?php

namespace App\Logging;

use App\Models\LogEntry;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

/**
 * Persists log records to the `log_entries` table so the admin dashboard's
 * live Logs panel has something reliable to poll.
 *
 * Why not just tail storage/logs/laravel.log? On Render (and most container
 * platforms) local disk is ephemeral and NOT shared across instances — a
 * file-based tail only ever shows whichever single container happened to
 * answer the admin's HTTP request, and loses everything on every redeploy
 * or restart. Writing to the database instead makes the log visible from
 * any instance and durable across deploys.
 *
 * Trade-off, on purpose: this adds one DB insert per log line that passes
 * the level filter. That's fine at Oudaa's current volume. If a future
 * tenant starts producing high-frequency debug/info logging, raise
 * LOG_LEVEL for this channel (see config/logging.php) rather than lowering
 * it, or move this handler onto a queued job.
 */
class DatabaseLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        try {
            LogEntry::create([
                'level' => strtolower($record->level->name),
                'message' => Str::limit($record->message, 1000),
                'context' => $this->sanitizeContext($record->context),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never let logging-to-the-database *itself* throw and break
            // the request (or worse, recurse back into the logger). If the
            // DB is down, the 'stderr' channel in the stack still catches
            // this line — that's exactly why this handler lives in a stack
            // alongside stderr rather than replacing it.
        }
    }

    /**
     * Monolog context arrays often contain exceptions, models, or other
     * objects that aren't safely JSON-encodable (or that we don't want
     * dumped into a log row wholesale, e.g. full stack traces). Reduce to
     * a small, JSON-safe shape.
     */
    private function sanitizeContext(array $context): array
    {
        $safe = [];

        foreach ($context as $key => $value) {
            if ($value instanceof \Throwable) {
                $safe[$key] = [
                    'class' => get_class($value),
                    'message' => Str::limit($value->getMessage(), 500),
                    'file' => $value->getFile().':'.$value->getLine(),
                ];
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $safe[$key] = is_string($value) ? Str::limit($value, 500) : $value;
                continue;
            }

            if (is_array($value)) {
                $safe[$key] = Str::limit(json_encode($value) ?: '', 500);
                continue;
            }

            // Objects without a sane string form (Closures, resources, etc.)
            $safe[$key] = is_object($value) ? get_class($value) : gettype($value);
        }

        return $safe;
    }
}
