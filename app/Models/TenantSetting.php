<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToCommunity;

/**
 * Lives on the 'tenant' connection — same dynamic-per-request connection
 * as every other model in this app (Fund, Fee, Resident, Committee...).
 * Single row per tenant database, seeded during provisioning.
 */
class TenantSetting extends Model
{
    use BelongsToCommunity;

    protected $fillable = [
        'community_name',
        'community_type',
    ];

    public static function current(): ?self
    {
        return static::query()->first();
    }

    public function isCondo(): bool
    {
        return $this->community_type === 'condo';
    }
}
