<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

/**
 * The one record that represents a community/tenant. Lives in the
 * same shared database as everything else — no separate connection
 * needed. It's the one table with no community_id column, since it's
 * the thing every other table's community_id points back at.
 */
class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'community_type',
        'owner_email',
        'status',
        'setup_token',
        'setup_token_expires_at',
        'provisioning_error',
    ];

    protected $hidden = [
        'setup_token',
    ];

    protected function casts(): array
    {
        return [
            'setup_token_expires_at' => 'datetime',
        ];
    }
}
