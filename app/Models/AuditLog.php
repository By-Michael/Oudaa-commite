<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCommunity;

class AuditLog extends Model
{
    use BelongsToCommunity;

    const UPDATED_AT = null;

    protected $fillable = [
        'committee_id',
        'committee_name',
        'via_god_admin',
        'god_admin_name',
        'action',
        'subject_type',
        'subject_id',
        'description',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * The one and only way a row is ever written to this table.
     * There is intentionally no update() or delete() usage anywhere
     * in the app for this model — it is append-only by convention.
     */
    public static function record(string $action, string $subjectType, ?int $subjectId, string $description): void
    {
        $committee = auth()->user();

        // While a god-admin is impersonating a committee member (see
        // ImpersonationBridgeController::redeem), every change they make
        // still runs as that member's own account — otherwise the log
        // would show it came from an admin acting on the community's
        // records directly, which they don't. This stamps *which* admin
        // it actually was, without hiding whose account was used.
        $viaAdmin = (bool) session('impersonated_by_god_admin');
        $adminName = session('god_admin_name');

        static::create([
            'committee_id' => $committee?->id,
            'committee_name' => $committee?->name,
            'via_god_admin' => $viaAdmin,
            'god_admin_name' => $viaAdmin ? $adminName : null,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);
    }
}
