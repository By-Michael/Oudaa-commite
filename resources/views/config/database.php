<?php

return [
    // One connection for the whole app. There is no more per-tenant
    // SQLite file and no more request-time connection swapping —
    // every community's data lives in this one database, separated
    // only by the community_id column (see BelongsToCommunity trait).
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // Kept available for local dev / tests via DB_CONNECTION=sqlite.
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'oudaa'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            // Aiven's free MySQL requires TLS and won't accept plain
            // connections at all. DB_MYSQL_SSL_CA should point at the
            // ca.pem you download from the Aiven console (service
            // overview page -> "CA certificate"). Without it PDO will
            // refuse the connection outright, so this isn't optional
            // for Aiven specifically — self-hosted MySQL without SSL
            // is fine leaving DB_MYSQL_SSL_CA unset.
            'options' => extension_loaded('pdo_mysql') && env('DB_MYSQL_SSL_CA') ? [
                PDO::MYSQL_ATTR_SSL_CA => env('DB_MYSQL_SSL_CA'),
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => env('DB_MYSQL_SSL_VERIFY', true),
            ] : [],
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
