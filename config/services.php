<?php

return [
    // Shared secret used to authenticate the separate God Admin dashboard.
    // Must match the "Agent API secret" entered for this instance there.
    // Generate with: php artisan tinker --execute="echo bin2hex(random_bytes(32));"
    'admin_agent' => [
        'secret' => env('ADMIN_AGENT_SECRET'),
    ],

    // Reuses the same MAIL_* keys already in .env (Brevo SMTP) —
    // only the sending mechanism (PHPMailer instead of Laravel Mail) changed.
    'phpmailer' => [
        'host' => env('MAIL_HOST', 'smtp-relay.brevo.com'),
        'port' => env('MAIL_PORT', 587),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'timeout' => env('MAIL_TIMEOUT', 10),
        'from_address' => env('MAIL_FROM_ADDRESS'),
        'from_name' => env('MAIL_FROM_NAME', 'Oudaa'),
    ],
];
