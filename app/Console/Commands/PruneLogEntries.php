<?php

namespace App\Console\Commands;

use App\Models\LogEntry;
use Illuminate\Console\Command;

/**
 * The 'database' log channel (see App\Logging\DatabaseLogHandler) inserts
 * a row per log line, so this table needs a retention policy or it grows
 * without bound. The admin dashboard's Logs panel only ever reads the most
 * recent ~200 rows anyway, so keeping a short window is free.
 */
class PruneLogEntries extends Command
{
    protected $signature = 'logs:prune {--days=7 : Delete log_entries older than this many days}';

    protected $description = 'Delete old rows from log_entries (used by the admin dashboard live Logs panel)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = LogEntry::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} log_entries older than {$days} day(s).");

        return self::SUCCESS;
    }
}
