<?php

return [
    'default' => env('LOG_CHANNEL', 'stack'),
    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'stderr', 'database'],
            'ignore_exceptions' => false,
        ],
        // Kept for local development, where disk is real and persistent.
        // Not relied on in production — see the 'stderr'/'database' notes
        // below.
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        // Render (and most container platforms) only capture stdout/stderr,
        // never files written to the container's disk — so anything sent
        // only to 'single' is invisible in the Render dashboard and gets
        // wiped on every redeploy.
        'stderr' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        // Backs the admin dashboard's live Logs panel (see
        // AgentApiController::recentLogs), which is intentionally a tail of
        // everything (info and up), not just errors. Writes to the
        // log_entries table instead of a local file, so the panel works no
        // matter which instance answers the request and survives
        // redeploys. Level is separate from LOG_LEVEL — every line here is
        // a DB insert, so 'debug' is excluded by default even if LOG_LEVEL
        // is 'debug'. Raise via LOG_DB_LEVEL to 'warning' if a tenant's
        // info-level volume ever makes this table grow too fast; see also
        // the `logs:prune` command (App\Console\Commands\PruneLogEntries),
        // scheduled daily in App\Console\Kernel.
        'database' => [
            'driver' => 'monolog',
            'handler' => App\Logging\DatabaseLogHandler::class,
            'level' => env('LOG_DB_LEVEL', 'info'),
        ],
        'null' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ],
    ],
];
