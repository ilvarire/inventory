<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether rate limiting is enabled globally.
    | When set to false, no rate limits will be enforced.
    |
    */

    'enabled' => env('RATE_LIMIT_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | The cache driver to use for storing rate limit data.
    | Options: database, redis, memcached, file
    | Redis is recommended for production.
    |
    */

    'driver' => env('RATE_LIMIT_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limits by Endpoint Type
    |--------------------------------------------------------------------------
    |
    | Define rate limits for different types of endpoints.
    | Format: requests per minute
    |
    */

    'limits' => [
        // Authentication endpoints
        'auth' => [
            'login' => [
                'max_attempts' => env('RATE_LIMIT_LOGIN', 5),
                'decay_minutes' => 1,
            ],
            'register' => [
                'max_attempts' => env('RATE_LIMIT_REGISTER', 3),
                'decay_minutes' => 60,
            ],
            'password_reset' => [
                'max_attempts' => env('RATE_LIMIT_PASSWORD_RESET', 3),
                'decay_minutes' => 60,
            ],
        ],

        // API endpoints
        'api' => [
            // Read operations (GET)
            'read' => [
                'guest' => env('RATE_LIMIT_READ_GUEST', 60),
                'user' => env('RATE_LIMIT_READ_USER', 120),
                'admin' => env('RATE_LIMIT_READ_ADMIN', 300),
            ],

            // Write operations (POST, PUT, DELETE)
            'write' => [
                'user' => env('RATE_LIMIT_WRITE_USER', 60),
                'admin' => env('RATE_LIMIT_WRITE_ADMIN', 120),
            ],

            // Report generation
            'reports' => [
                'user' => env('RATE_LIMIT_REPORTS_USER', 30),
                'admin' => env('RATE_LIMIT_REPORTS_ADMIN', 60),
            ],

            // Export operations (Excel/PDF)
            'exports' => [
                'user' => env('RATE_LIMIT_EXPORTS_USER', 10),
                'admin' => env('RATE_LIMIT_EXPORTS_ADMIN', 20),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Messages
    |--------------------------------------------------------------------------
    |
    | Custom messages for rate limit responses.
    |
    */

    'messages' => [
        'too_many_requests' => 'Too many requests. Please try again later.',
        'auth_throttle' => 'Too many authentication attempts. Please try again in :seconds seconds.',
        'export_throttle' => 'Export limit reached. Please wait before requesting another export.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Log rate limit violations for monitoring and security.
    |
    */

    'log_violations' => env('RATE_LIMIT_LOG_VIOLATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | Include rate limit information in response headers.
    |
    */

    'include_headers' => env('RATE_LIMIT_INCLUDE_HEADERS', true),

];
