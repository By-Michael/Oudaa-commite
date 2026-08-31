<?php

namespace App\Support;

/**
 * Replaces the old "swap the DB connection's file path" trick from the
 * per-tenant-SQLite-file version of this app. Now that every community
 * shares one database, there's nothing to swap connections-wise — we
 * just need a per-request "which community_id am I" value that the
 * BelongsToCommunity trait reads from.
 *
 * Bound as a singleton in AppServiceProvider. On plain PHP-FPM /
 * `artisan serve` the container (and this instance) is torn down and
 * rebuilt fresh every request, so there's no cross-request leakage
 * risk. If this app ever moves to Octane/swoole (long-lived worker,
 * container persists across requests), rebind this as `$app->scoped()`
 * instead of `singleton()` so Octane resets it between requests.
 */
class CurrentCommunity
{
    protected ?int $id = null;

    public function set(?int $id): void
    {
        $this->id = $id;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function clear(): void
    {
        $this->id = null;
    }
}
