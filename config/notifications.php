<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notifications Enabled
    |--------------------------------------------------------------------------
    |
    | This option controls whether email notifications are enabled globally.
    | When set to false, no emails will be sent regardless of user preferences.
    |
    */

    'enabled' => env('NOTIFICATIONS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Notifications
    |--------------------------------------------------------------------------
    |
    | When enabled, notifications will be queued for background processing
    | instead of being sent immediately. This improves response times.
    |
    */

    'queue' => env('NOTIFICATIONS_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Available notification channels. You can enable/disable specific channels.
    |
    */

    'channels' => [
        'email' => env('NOTIFICATIONS_CHANNEL_EMAIL', true),
        'database' => env('NOTIFICATIONS_CHANNEL_DATABASE', true),
        'sms' => env('NOTIFICATIONS_CHANNEL_SMS', false),
        'slack' => env('NOTIFICATIONS_CHANNEL_SLACK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    |
    | Configuration for specific notification types.
    |
    */

    'types' => [
        'low_stock' => [
            'enabled' => true,
            'recipients' => ['Manager', 'Admin', 'Procurement'],
        ],
        'expiry_alert' => [
            'enabled' => true,
            'recipients' => ['Manager', 'Admin', 'Store Keeper'],
        ],
        'high_wastage' => [
            'enabled' => true,
            'recipients' => ['Manager', 'Admin'],
        ],
        'pending_approval' => [
            'enabled' => true,
            'recipients' => ['Manager', 'Admin'],
        ],
        'approval_status' => [
            'enabled' => true,
            'recipients' => [], // Sent to requester
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for failed notification retries.
    |
    */

    'retry' => [
        'max_attempts' => env('NOTIFICATIONS_MAX_RETRIES', 3),
        'delay_seconds' => env('NOTIFICATIONS_RETRY_DELAY', 60),
    ],

];
