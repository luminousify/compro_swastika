<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shared Hosting Optimization Settings
    |--------------------------------------------------------------------------
    |
    | Configuration settings optimized for shared hosting environments
    |
    */

    'shared_hosting' => [
        /*
        |--------------------------------------------------------------------------
        | Memory Optimization
        |--------------------------------------------------------------------------
        */
        'memory_limit' => '256M',
        'max_execution_time' => 60,
        
        /*
        |--------------------------------------------------------------------------
        | File System Optimization
        |--------------------------------------------------------------------------
        */
        'file_cache_ttl' => 3600, // 1 hour
        'settings_cache_ttl' => 7200, // 2 hours
        
        /*
        |--------------------------------------------------------------------------
        | Database Connection Pool
        |--------------------------------------------------------------------------
        */
        'database' => [
            'max_connections' => 10,
            'connection_timeout' => 30,
            'reconnect' => true,
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Asset Optimization
        |--------------------------------------------------------------------------
        */
        'assets' => [
            'enable_compression' => true,
            'enable_minification' => true,
            'cache_bust' => true,
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Logging Configuration
        |--------------------------------------------------------------------------
        */
        'logging' => [
            'level' => env('LOG_LEVEL', 'warning'),
            'max_files' => 5,
            'rotate_daily' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'slow_query_threshold' => 2.0, // seconds
        'memory_usage_threshold' => 0.8, // 80% of limit
        'response_time_threshold' => 5.0, // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'default_ttl' => 3600,
        'long_ttl' => 86400, // 24 hours
        'tags' => [
            'settings' => 'settings',
            'content' => 'content',
            'navigation' => 'nav',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Deployment Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the deployment functionality for shared hosting
    | environments where SSH access is not available.
    |
    */
    
    // Enable/disable deployment routes
    'enabled' => env('DEPLOYMENT_ENABLED', false),
    
    // Secret key for deployment authentication
    'secret_key' => env('DEPLOYMENT_KEY', 'your-secret-deployment-key-2024'),
];