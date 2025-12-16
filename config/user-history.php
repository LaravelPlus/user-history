<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | User History Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the user history package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Auto Record Events
    |--------------------------------------------------------------------------
    |
    | Automatically record model events (created, updated, deleted, restored)
    | when using the HasUserActivity trait.
    |
    */
    'auto_record_events' => env('USER_HISTORY_AUTO_RECORD', true),

    /*
    |--------------------------------------------------------------------------
    | Track IP Address
    |--------------------------------------------------------------------------
    |
    | Whether to track the IP address of the user performing the activity.
    |
    */
    'track_ip_address' => env('USER_HISTORY_TRACK_IP', true),

    /*
    |--------------------------------------------------------------------------
    | Track User Agent
    |--------------------------------------------------------------------------
    |
    | Whether to track the user agent of the user performing the activity.
    |
    */
    'track_user_agent' => env('USER_HISTORY_TRACK_USER_AGENT', true),

    /*
    |--------------------------------------------------------------------------
    | Track Properties
    |--------------------------------------------------------------------------
    |
    | Whether to track model properties/changes in the activity log.
    |
    */
    'track_properties' => env('USER_HISTORY_TRACK_PROPERTIES', true),

    /*
    |--------------------------------------------------------------------------
    | Activity Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep activity records before automatic cleanup.
    | Set to null to disable automatic cleanup.
    |
    */
    'retention_days' => env('USER_HISTORY_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of activities to show per page.
    |
    */
    'per_page' => env('USER_HISTORY_PER_PAGE', 15),

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widget
    |--------------------------------------------------------------------------
    |
    | Configuration for the dashboard activity widget.
    |
    */
    'dashboard' => [
        'enabled' => env('USER_HISTORY_DASHBOARD_ENABLED', true),
        'limit' => env('USER_HISTORY_DASHBOARD_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Actions
    |--------------------------------------------------------------------------
    |
    | Actions that should not be recorded in the activity log.
    |
    */
    'excluded_actions' => [
        // Add actions to exclude here
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Model classes that should not be tracked for activities.
    |
    */
    'excluded_models' => [
        // Add model classes to exclude here
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be tracked when recording update activities.
    | Leave empty to track all fields.
    |
    */
    'tracked_fields' => [
        // Add specific fields to track here
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be ignored when recording update activities.
    |
    */
    'ignored_fields' => [
        'updated_at',
        'created_at',
        'deleted_at',
        'remember_token',
        'password',
        'password_confirmation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the package routes.
    |
    */
    'routes' => [
        'enabled' => env('USER_HISTORY_ROUTES_ENABLED', true),
        'prefix' => env('USER_HISTORY_ROUTES_PREFIX', 'admin/user-history'),
        'middleware' => ['web', 'auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the package API routes.
    |
    */
    'api' => [
        'enabled' => env('USER_HISTORY_API_ENABLED', true),
        'prefix' => env('USER_HISTORY_API_PREFIX', 'api/user-history'),
        'middleware' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | Configuration for activity notifications.
    |
    */
    'notifications' => [
        'enabled' => env('USER_HISTORY_NOTIFICATIONS_ENABLED', false),
        'channels' => ['mail', 'database'],
        'events' => [
            'user_login' => true,
            'user_logout' => true,
            'password_changed' => true,
            'profile_updated' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for activity export functionality.
    |
    */
    'export' => [
        'enabled' => env('USER_HISTORY_EXPORT_ENABLED', true),
        'formats' => ['csv', 'json'],
        'max_records' => env('USER_HISTORY_EXPORT_MAX_RECORDS', 10000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    | Enable or disable user activity features by adding/removing feature classes.
    */
    'features' => [
        LaravelPlus\UserHistory\Features\ModelEvents::class,
        LaravelPlus\UserHistory\Features\PageVisits::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Middleware
    |--------------------------------------------------------------------------
    | Register the user activity middleware globally or as deferred (opt-in).
    | If 'defer' is true, middleware uses Laravel's defer() method to execute
    | after the response is sent to the user (doesn't slow down page load).
    */
    'global_middleware' => [
        'enabled' => true,
        'defer' => false, // Use deferred execution to not slow down page load
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Paths
    |--------------------------------------------------------------------------
    |
    | Paths that should not be tracked for page visits.
    | Uses fnmatch() for pattern matching.
    |
    */
    'excluded_paths' => [
        'admin/user-history', // Don't track the user history dashboard itself
        'admin/user-history/*', // Don't track any user history pages
        'home', // Skip home page visits
        'welcome', // Skip welcome page visits
        'login', // Skip login page visits (optional)
        'logout', // Skip logout page visits (optional)
        'api/*', // Skip API routes
        'ajax/*', // Skip AJAX routes
        'assets/*', // Skip asset requests
        'css/*', // Skip CSS files
        'js/*', // Skip JavaScript files
        'images/*', // Skip image files
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Visit Tracking Mode
    |--------------------------------------------------------------------------
    |
    | Control how page visits are tracked.
    | 'all' - Track all page visits (except excluded paths)
    | 'important' - Only track pages that might indicate user engagement
    | 'minimal' - Only track specific important actions
    |
    */
    'page_visit_tracking' => env('USER_HISTORY_PAGE_VISIT_TRACKING', 'all'),

    /*
    |--------------------------------------------------------------------------
    | Important Paths (for 'important' tracking mode)
    |--------------------------------------------------------------------------
    |
    | Paths that should always be tracked when using 'important' mode.
    | These are typically pages that indicate user engagement.
    |
    */
    'important_paths' => [
        'finances/*', // Financial pages
        'admin/*', // Admin pages (except user-history)
        'reports/*', // Report pages
        'analytics/*', // Analytics pages
        'settings/*', // Settings pages
        'profile/*', // Profile pages
    ],
];
