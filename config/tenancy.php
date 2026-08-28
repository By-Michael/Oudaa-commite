<?php

return [
    // Slugs that can never be claimed by a community, because they'd
    // collide with a real app route (landing pages, wizard, auth, etc.)
    // or because they're confusing/reserved for future platform use.
    'reserved_slugs' => [
        'about', 'services', 'service', 'portfolio', 'blog', 'contact',
        'pricing', 'create', 'login', 'logout', 'admin', 'dashboard',
        'set-password', 'settings', 'api', 'storage', 'app', 'www',
        'oudaa', 'support', 'help', 'terms', 'privacy', 'assets',
    ],

    // Where per-tenant SQLite files live.
    'database_path' => database_path('tenants'),

    // How long a "set your password" link stays valid after signup.
    'setup_link_ttl_hours' => 24 * 7,
];
