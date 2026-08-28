<?php

namespace Database\Seeders;

use App\Models\Committee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
