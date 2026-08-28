<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'committees',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'committees',
        ],
    ],

    'providers' => [
        'committees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Committee::class,
        ],
    ],

    'passwords' => [
        'committees' => [
            'provider' => 'committees',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
