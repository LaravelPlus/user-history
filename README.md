# Laravel Plus User History

A comprehensive Laravel package for tracking user activities and history with advanced features like automatic event recording, detailed analytics, and flexible configuration.

## Features

- 🔍 **Automatic Activity Tracking** - Automatically record model events (created, updated, deleted, restored)
- 👤 **User Activity Monitoring** - Track user actions with IP address and user agent
- 📊 **Advanced Analytics** - Built-in statistics, charts, and reporting
- 🎯 **Flexible Filtering** - Filter activities by user, action, date range, and more
- 📱 **API Support** - RESTful API endpoints for integration
- 🎨 **Dashboard Widget** - Ready-to-use dashboard components
- 📤 **Export Functionality** - Export activities to CSV and JSON formats
- 🔔 **Notification System** - Configurable notifications for specific events
- 🧹 **Auto Cleanup** - Automatic cleanup of old activity records
- 🛡️ **Security Features** - IP tracking, user agent logging, and data retention policies

## Requirements

- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- spatie/laravel-permission 5.x or 6.x

## Installation

1. **Install the package via Composer:**

```bash
composer require laravelplus/user-history
```

2. **Publish the configuration file:**

```bash
php artisan vendor:publish --provider="LaravelPlus\UserHistory\Providers\UserHistoryServiceProvider"
```

3. **Run the migrations:**

```bash
php artisan migrate
```

## Configuration

The package configuration is located in `config/user-history.php`. Here are the key configuration options:

### Environment Variables

```env
# Enable/disable automatic event recording
USER_HISTORY_AUTO_RECORD=true

# Track IP address
USER_HISTORY_TRACK_IP=true

# Track user agent
USER_HISTORY_TRACK_USER_AGENT=true

# Track model properties/changes
USER_HISTORY_TRACK_PROPERTIES=true

# Activity retention in days (null to disable cleanup)
USER_HISTORY_RETENTION_DAYS=365

# Items per page
USER_HISTORY_PER_PAGE=15

# Dashboard widget settings
USER_HISTORY_DASHBOARD_ENABLED=true
USER_HISTORY_DASHBOARD_LIMIT=10

# Route settings
USER_HISTORY_ROUTES_ENABLED=true
USER_HISTORY_ROUTES_PREFIX=admin/user-history

# API settings
USER_HISTORY_API_ENABLED=true
USER_HISTORY_API_PREFIX=api/user-history

# Notifications
USER_HISTORY_NOTIFICATIONS_ENABLED=false

# Export settings
USER_HISTORY_EXPORT_ENABLED=true
USER_HISTORY_EXPORT_MAX_RECORDS=10000
```

## Usage

### Basic Usage

1. **Add the trait to your models:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelPlus\UserHistory\Traits\HasUserActivity;

class Post extends Model
{
    use HasUserActivity;

    // Your model code...
}
```

2. **The trait automatically records activities for:**
   - Model creation (`created` event)
   - Model updates (`updated` event)
   - Model deletion (`deleted` event)
   - Model restoration (`restored` event) - if using SoftDeletes

### Manual Activity Recording

```php
// Record a custom activity
$post->recordActivity('published', 'Post was published');

// Record with additional properties
$post->recordActivity('status_changed', 'Post status updated', [
    'old_status' => 'draft',
    'new_status' => 'published'
]);

// Record with metadata
$post->recordActivity('viewed', 'Post was viewed', [], [
    'session_id' => session()->getId(),
    'referrer' => request()->header('referer')
]);
```

### Activity Service

```php
use LaravelPlus\UserHistory\Services\UserActivityService;

$service = new UserActivityService();

// Get activities with filters
$activities = $service->getActivities([
    'user_id' => 1,
    'action' => 'created',
    'date_from' => now()->subDays(7),
    'date_to' => now()
]);

// Get paginated activities
$paginatedActivities = $service->getPaginatedActivities([
    'user_id' => 1
], 20);

// Get activity statistics
$stats = $service->getActivityStats([
    'date_from' => now()->subMonth()
]);

// Export activities
$csvFile = $service->exportToCsv([
    'user_id' => 1,
    'action' => 'created'
]);
```

### Model Relationships

```php
// Get all activities for a model
$post->activities;

// Get activities for a specific user
$user->activities;

// Get recent activities
$recentActivities = UserActivity::with(['user', 'subject'])
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

## Routes

The package provides several routes for managing user activities:

### Web Routes

- `GET /admin/user-history` - Dashboard
- `GET /admin/user-history/activities` - Activity listing
- `GET /admin/user-history/stats` - Statistics
- `GET /admin/user-history/users` - User listing
- `GET /admin/user-history/users/{user}` - User profile
- `GET /admin/user-history/reports/export` - Export reports

### API Routes

- `GET /api/user-history/activities` - Get activities
- `GET /api/user-history/stats` - Get statistics
- `GET /api/user-history/chart-data` - Get chart data
- `GET /api/user-history/search` - Search activities

## Database Schema

The package creates a `user_activities` table with the following structure:

```sql
CREATE TABLE user_activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id BIGINT UNSIGNED NULL,
    properties JSON NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_action_created (action, created_at),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created (created_at),
    INDEX idx_ip (ip_address),
    INDEX idx_subject_created (subject_type, subject_id, created_at)
);
```

## Advanced Features

### Custom Activity Names

Override the `getActivitySubjectName()` method in your models:

```php
public function getActivitySubjectName(): string
{
    return $this->title ?? $this->name ?? 'Post #' . $this->id;
}
```

### Custom Activity URLs

```php
public function getActivitySubjectUrl(): ?string
{
    return route('posts.show', $this);
}
```

### Excluding Models/Fields

Add to your configuration:

```php
// Exclude specific models
'excluded_models' => [
    \App\Models\Log::class,
    \App\Models\Cache::class,
],

// Exclude specific fields from tracking
'ignored_fields' => [
    'updated_at',
    'created_at',
    'deleted_at',
    'remember_token',
    'password',
],
```

### Custom Activity Descriptions

```php
// Override the default description
protected function getDefaultActivityDescription(string $action): string
{
    return match ($action) {
        'created' => "New post '{$this->title}' was created",
        'updated' => "Post '{$this->title}' was updated",
        'deleted' => "Post '{$this->title}' was deleted",
        default => parent::getDefaultActivityDescription($action),
    };
}
```

## Artisan Commands

The package provides several Artisan commands:

```bash
# Clean old activities
php artisan user-history:cleanup

# Export activities
php artisan user-history:export

# Generate activity report
php artisan user-history:report
```

## Events and Listeners

The package dispatches events that you can listen to:

```php
// In your EventServiceProvider
protected $listen = [
    'LaravelPlus\UserHistory\Events\ActivityRecorded' => [
        'App\Listeners\LogActivityToExternalService',
    ],
];
```

## Testing

```bash
# Run the package tests
composer test

# Run with coverage
composer test -- --coverage
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, please open an issue on GitHub or contact the LaravelPlus team at team@laravelplus.com.

## Changelog

### v1.0.0
- Initial release
- Automatic activity tracking
- Dashboard and analytics
- API endpoints
- Export functionality
- Notification system 