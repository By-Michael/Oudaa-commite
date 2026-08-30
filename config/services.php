<?php

return [
    // Shared secret used to authenticate the separate God Admin dashboard.
    // Must match the "Agent API secret" entered for this instance there.
    // Generate with: php artisan tinker --execute="echo bin2hex(random_bytes(32));"
    'admin_agent' => [
        'secret' => env('ADMIN_AGENT_SECRET'),
    ],
];
