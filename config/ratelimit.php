<?php

use App\Core\Support\Env;

return [
    'enabled' => Env::get('RATE_LIMIT_ENABLED', true),

    'rules' => [
        'auth' => [
            'routes' => ['/login', '/register', '/lost-password', '/reset-password'],
            'limit' => (int)Env::get('RATE_LIMIT_AUTH_LIMIT', 5),
            'window' => (int)Env::get('RATE_LIMIT_AUTH_WINDOW', 300),
            'key_type' => 'ip',
        ],

        'voting' => [
            'routes' => ['/vote', '/vote/*'],
            'limit' => (int)Env::get('RATE_LIMIT_VOTING_LIMIT', 10),
            'window' => (int)Env::get('RATE_LIMIT_VOTING_WINDOW', 60),
            'key_type' => 'ip',
        ],

        'server_actions' => [
            'routes' => ['/submit', '/edit-server/*', '/server-claim/*/start', '/server-claim/*/verify'],
            'limit' => (int)Env::get('RATE_LIMIT_SERVER_ACTIONS_LIMIT', 5),
            'window' => (int)Env::get('RATE_LIMIT_SERVER_ACTIONS_WINDOW', 60),
            'key_type' => 'user',
        ],

        'contact' => [
            'routes' => ['/contact', '/report'],
            'limit' => (int)Env::get('RATE_LIMIT_CONTACT_LIMIT', 3),
            'window' => (int)Env::get('RATE_LIMIT_CONTACT_WINDOW', 300),
            'key_type' => 'ip',
        ],

        'payments' => [
            'routes' => ['/paypal/create-order', '/paypal/capture-payment'],
            'limit' => (int)Env::get('RATE_LIMIT_PAYMENTS_LIMIT', 10),
            'window' => (int)Env::get('RATE_LIMIT_PAYMENTS_WINDOW', 300),
            'key_type' => 'user',
        ],

        'api' => [
            'routes' => ['/api/*', '/comment/load-more'],
            'limit' => (int)Env::get('RATE_LIMIT_API_LIMIT', 60),
            'window' => (int)Env::get('RATE_LIMIT_API_WINDOW', 60),
            'key_type' => 'ip',
        ],

        'admin' => [
            'routes' => ['/admin/*'],
            'limit' => (int)Env::get('RATE_LIMIT_ADMIN_LIMIT', 200),
            'window' => (int)Env::get('RATE_LIMIT_ADMIN_WINDOW', 60),
            'key_type' => 'user',
        ],

        'general' => [
            'routes' => ['*'],
            'limit' => (int)Env::get('RATE_LIMIT_GENERAL_LIMIT', 150),
            'window' => (int)Env::get('RATE_LIMIT_GENERAL_WINDOW', 60),
            'key_type' => 'ip',
        ],
    ],

    'bypass' => [
        'ips' => array_map('trim', explode(',', Env::get('RATE_LIMIT_BYPASS_IPS', '127.0.0.1'))),
        'roles' => ['2'],
    ],

    'responses' => [
        'too_many_requests' => [
            'message' => 'Too many requests. Please try again later.',
            'retry_after_header' => true,
        ],
    ],

    'logging' => [
        'enabled' => true,
        'log_violations' => true,
        'log_file' => __DIR__ . '/../storage/logs/ratelimit.log',
    ],
];
