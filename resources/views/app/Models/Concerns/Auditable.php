<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;

/**
 * Attach to any model whose changes should be recorded in the
 * append-only audit log. Hooks into Eloquent's created/updated events
 * so every controller path (store, update, the deactivate/archive
 * toggles) is covered automatically without each controller having to
 * remember to log anything.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLog::record(
                'created',
                class_basename($model),
                $model->id,
                class_basename($model)." \"{$model->auditLabel()}\" created."
            );
        });

        static::updated(function ($model) {
            $changed = array_keys($model->getChanges());
            $changed = array_diff($changed, ['updated_at', 'remember_token']);
            $changed = array_values($changed);

            if (empty($changed)) {
                return;
            }

            if ($changed === ['status']) {
                $action = strtolower((string) $model->status);
                $description = class_basename($model)." \"{$model->auditLabel()}\" status changed to {$model->status}.";
            } else {
                $action = 'updated';
                $description = class_basename($model)." \"{$model->auditLabel()}\" updated (".implode(', ', $changed).').';
            }

            AuditLog::record($action, class_basename($model), $model->id, $description);
        });
    }

    /**
     * Human-readable label for this row, used in log descriptions.
     * Override on the model if "name" isn't the right field.
     */
    public function auditLabel(): string
    {
        return (string) ($this->name ?? $this->id);
    }
}
