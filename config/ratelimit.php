<?php

return [
    // Global enable/disable switch
    'enabled' => true,
    
    // Rate limit rules by route pattern
    'rules' => [
        // Authentication routes - loose enough for typos, strict enough for brute force
        'auth' => [
            'routes' => ['/login', '/register', '/lost-password', '/reset-password'],
            'limit' => 5,
            'window' => 300, // 5 requests every 5 minutes
            'key_type' => 'ip',
        ],
        
        // Voting endpoints network shield - stops API spam (business rules are in database)
        'voting' => [
            'routes' => ['/vote', '/vote/*'],
            'limit' => 10,
            'window' => 60, // 10 requests per minute
            'key_type' => 'ip',
        ],
        
        // Server actions (creation, editing, claims)
        'server_actions' => [
            'routes' => ['/submit', '/edit-server/*', '/server-claim/*/start', '/server-claim/*/verify'],
            'limit' => 5,
            'window' => 60, // 5 requests per minute
            'key_type' => 'user',
        ],
        
        // Contact and reports - moderate
        'contact' => [
            'routes' => ['/contact', '/report'],
            'limit' => 3,
            'window' => 300, // 3 submissions every 5 minutes
            'key_type' => 'ip',
        ],
        
        // API actions - generous
        'api' => [
            'routes' => ['/api/*', '/comment/load-more'],
            'limit' => 60,
            'window' => 60, // 60 requests per minute
            'key_type' => 'ip',
        ],
        
        // Admin routes - higher limits
        'admin' => [
            'routes' => ['/admin/*'],
            'limit' => 200,
            'window' => 60, // 200 requests per minute
            'key_type' => 'user',
        ],
        
        // General browsing - very high to prevent false-positives
        'general' => [
            'routes' => ['*'],
            'limit' => 150,
            'window' => 60, // 150 page views per minute
            'key_type' => 'ip',
        ],
    ],
    
    // Bypass settings
    'bypass' => [
        // IPs that bypass all rate limiting
        'ips' => [
            '127.0.0.1', // Whitelist localhost
        ],
        
        // User roles that bypass rate limiting
        'roles' => [
            '2', // Admin role type 2 bypasses limits
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
