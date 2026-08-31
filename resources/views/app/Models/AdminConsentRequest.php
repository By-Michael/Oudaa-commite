<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminConsentRequest extends Model
{
    protected $fillable = [
        'token', 'committee_id', 'tenant_slug', 'reason',
        'callback_url', 'status', 'expires_at', 'responded_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function isPendingAndLive(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
