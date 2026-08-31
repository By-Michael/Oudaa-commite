<?php

namespace Database\Seeders;

use App\Models\Central\Tenant;
use App\Models\Committee;
use App\Support\CurrentCommunity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Committees only make sense scoped to a community now (see
     * BelongsToCommunity) — a committee row with no community_id
     * won't match any tenant's global scope, so it'd be an orphaned,
     * unreachable row. For local dev, seed one demo community and its
     * admin the same way a real signup would create them.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Community',
                'community_type' => 'normal',
                'owner_email' => 'admin@committee.local',
                'status' => 'active',
            ]
        );

        app(CurrentCommunity::class)->set($tenant->id);

        // Default committee login for first access.
        // CHANGE THIS PASSWORD after first login.
        Committee::firstOrCreate(
            ['email' => 'admin@committee.local'],
            [
                'name' => 'Committee Admin',
                'phone' => null,
                'password' => Hash::make('password'),
            ]
        );
    }
}
