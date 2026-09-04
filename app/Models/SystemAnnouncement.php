<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAnnouncement extends Model
{
    public $incrementing = true;

    protected $fillable = [
        'id', 'title', 'body', 'level', 'dismissible', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'dismissible' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function isLive(): bool
    {
        $now = now();

        return (! $this->starts_at || $this->starts_at->lte($now))
            && (! $this->ends_at || $this->ends_at->gte($now));
    }

    public function dismissals()
    {
        return $this->hasMany(SystemAnnouncementDismissal::class);
    }

    public function dismissedBy(int $committeeId): bool
    {
        return $this->dismissals()->where('committee_id', $committeeId)->exists();
    }
}
