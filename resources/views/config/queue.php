<?php

return [
    // sync is the default now (see .env.example) — provisioning runs
    // inline, no worker process needed. 'database' is kept below and
    // still fully wired up in case that ever needs to change back.
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => null, // app's default connection
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],
    ],

    'batching' => [
        'database' => null,
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => null,
        'table' => 'failed_jobs',
    ],
];
