<?php

return [
    'default' => env('QUEUE_CONNECTION', 'database'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        // Tenant provisioning is queued so signup returns instantly and
        // the (slower) DB-create + migrate + email work happens in the
        // background. Pinned to the 'central' connection deliberately —
        // the jobs table must live in the one fixed database, never in
        // whatever tenant SQLite file happens to be active when a job
        // is dispatched or picked up.
        'database' => [
            'driver' => 'database',
            'connection' => 'central',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],
    ],

    'batching' => [
        'database' => 'central',
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => 'central',
        'table' => 'failed_jobs',
    ],
];
