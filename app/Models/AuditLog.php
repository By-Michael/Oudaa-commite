<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'committee_id',
        'committee_name',
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

        static::create([
            'committee_id' => $committee?->id,
            'committee_name' => $committee?->name,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
        ]);
    }
}
