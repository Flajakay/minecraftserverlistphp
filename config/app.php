<?php

use App\Core\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Minecraft Server List'),
    'url' => Env::get('APP_URL', 'http://localhost:8080/'),
    'timezone' => Env::get('APP_TIMEZONE', 'America/New_York'),
    'db' => [
        'host' => Env::get('DB_HOST', 'db'),
        'username' => Env::get('DB_USERNAME', 'root'),
        'password' => Env::get('DB_PASSWORD', 'root'),
        'database' => Env::get('DB_DATABASE', 'serverlist')
    ],
    'paypal' => [
        'email' => Env::get('PAYPAL_EMAIL', ''),
        'client_id' => Env::get('PAYPAL_CLIENT_ID', ''),
        'client_secret' => Env::get('PAYPAL_CLIENT_SECRET', ''),
        'sandbox' => Env::get('PAYPAL_SANDBOX', true)
    ]
];
