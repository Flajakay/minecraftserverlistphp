<?php

return [
    // Global rate limiting configuration
    
    // Rate limit rules by route pattern
    'rules' => [
        // Authentication routes - stricter limits
        'auth' => [
            'routes' => ['/login', '/register', '/reset-password'],
            'limit' => 1,
            'window' => 900, // 15 minutes (in seconds)
            'key_type' => 'ip', // ip, user, combined
        ],
        
        // Voting routes - very strict
        'voting' => [
            'routes' => ['/vote/*', '/servers/*/vote'],
            'limit' => 1,
            'window' => 86400, // 24 hours (in seconds)
            'key_type' => 'combined',
        ],
        
        // Server submission/editing - moderate
        'server_actions' => [
            'routes' => ['/servers/submit', '/servers/*/edit', '/servers/create'],
            'limit' => 10,
            'window' => 3600, // 1 hour (in seconds)
            'key_type' => 'user',
        ],
        
        // Contact/reporting - moderate
        'contact' => [
            'routes' => ['/contact', '/servers/*/report'],
            'limit' => 3,
            'window' => 3600, // 1 hour (in seconds)
            'key_type' => 'ip',
        ],
        
        // API routes - moderate
        'api' => [
            'routes' => ['/api/*'],
            'limit' => 100,
            'window' => 3600, // 1 hour (in seconds)
            'key_type' => 'ip',
        ],
        
        // Admin routes - higher limits
        'admin' => [
            'routes' => ['/admin/*'],
            'limit' => 200,
            'window' => 3600, // 1 hour (in seconds)
            'key_type' => 'user',
        ],
        
        // General browsing - very high limits
        'general' => [
            'routes' => ['*'],
            'limit' => 1000,
            'window' => 3600, // 1 hour (in seconds)
            'key_type' => 'ip',
        ],
    ],
    
    // Bypass settings
    'bypass' => [
        // IPs that bypass all rate limiting
        'ips' => [
        ],
        
        // User roles that bypass rate limiting
        'roles' => [
            '2',
            // 'admin', // Uncomment to bypass for admins too
        ],
    ],
    
    // Error responses
    'responses' => [
        'too_many_requests' => [
            'message' => 'Too many requests. Please try again later.',
            'retry_after_header' => true, // Add Retry-After header
        ],
    ],
    
    // Logging
    'logging' => [
        'enabled' => true,
        'log_violations' => true,
        'log_file' => __DIR__ . '/../storage/logs/ratelimit.log',
    ],
];
