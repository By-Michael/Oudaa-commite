<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

/**
 * The one record, in the one central database, that represents a
 * community/tenant. Deliberately pinned to the 'central' connection
 * with an explicit property so it is never affected by the per-request
 * connection-swapping that App\Http\Middleware\ResolveTenant does to
 * the 'tenant' connection.
 */
class Tenant extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'slug',
        'community_type',
        'owner_email',
        'db_path',
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
