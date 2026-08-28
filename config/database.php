<?php

use Illuminate\Support\Str;

return [
    // Default connection for the app is 'tenant' — every existing model
    // (Fund, Fee, Resident, Committee/auth, etc.) is unmodified and just
    // uses whatever the default connection is. The ResolveTenant
    // middleware swaps *only* the 'tenant' connection's database path
    // per-request, based on the {tenant} slug in the URL, before any of
    // those models run a query. 'central' never moves — it's the one
    // fixed registry database that tracks every tenant (community) that
    // exists, their slugs, status, and where their SQLite file lives.
    'default' => env('DB_CONNECTION', 'tenant'),

    'connections' => [

        // The one central, never-changing database. Holds the tenants
        // registry, queue jobs, and anything else that is app-wide
        // rather than belonging to a single community.
        'central' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_CENTRAL_DATABASE', database_path('central.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        // Per-tenant database. The 'database' path below is only a
        // placeholder used outside an HTTP request (e.g. artisan
        // commands, queue worker boot). During a real request it is
        // always overwritten by App\Http\Middleware\ResolveTenant
        // before any query touches this connection.
        'tenant' => [
            'driver' => 'sqlite',
            'url' => null,
            'database' => env('DB_TENANT_PLACEHOLDER', database_path('tenants/_placeholder.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        // Kept for the day this moves off SQLite. Not wired up yet —
        // switching 'tenant' (and/or 'central') to this driver later is
        // a config change, not a rewrite, as long as connection names
        // stay the same everywhere in the codebase.
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'hivee_committee'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];
