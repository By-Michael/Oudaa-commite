<?php

namespace App\Models\Concerns;

use App\Support\CurrentCommunity;
use Illuminate\Database\Eloquent\Builder;

/**
 * Attach to every model that used to live in a tenant's own SQLite
 * file. Now they all live in one shared database, and this trait does
 * what "which .sqlite file am I connected to" used to do for free:
 *
 *   1. Every query against the model is automatically scoped to
 *      whatever community_id App\Http\Middleware\ResolveTenant put in
 *      CurrentCommunity for this request — so ResidentController,
 *      FundController, Auth::attempt(), etc. all keep working exactly
 *      as before without a single line of controller code changing.
 *   2. New rows get community_id auto-filled the same way, so
 *      ->create([...]) calls that never mentioned community_id (which
 *      is every one of them, on purpose — it must never be
 *      mass-assignable from user input) still get stamped correctly.
 *
 * IMPORTANT: this only protects query results, not raw DB::table(...)
 * or DB::select(...) calls — there are none of those in this codebase
 * today (confirm that stays true before adding one on a model that
 * uses this trait).
 */
trait BelongsToCommunity
{
    public static function bootBelongsToCommunity(): void
    {
        static::addGlobalScope('community', function (Builder $builder) {
            $id = app(CurrentCommunity::class)->id();

            if ($id !== null) {
                $builder->where($builder->getModel()->getTable().'.community_id', $id);
            }
        });

        static::creating(function ($model) {
            if (empty($model->community_id)) {
                $model->community_id = app(CurrentCommunity::class)->id();
            }
        });
    }

    public function scopeWithoutCommunityScope($query)
    {
        return $query->withoutGlobalScope('community');
    }
}
