# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Nothing yet

### Changed
- Nothing yet

### Deprecated
- Nothing yet

### Removed
- Nothing yet

### Fixed
- Nothing yet

### Security
- Nothing yet

## [1.0.0] - 2024-01-01

### Added
- Initial release of Laravel Plus User History package
- **Automatic Activity Tracking**: Automatically record model events (created, updated, deleted, restored)
- **User Activity Monitoring**: Track user actions with IP address and user agent
- **Advanced Analytics**: Built-in statistics, charts, and reporting capabilities
- **Flexible Filtering**: Filter activities by user, action, date range, and more
- **API Support**: RESTful API endpoints for integration with external systems
- **Dashboard Widget**: Ready-to-use dashboard components for Laravel applications
- **Export Functionality**: Export activities to CSV and JSON formats
- **Notification System**: Configurable notifications for specific events
- **Auto Cleanup**: Automatic cleanup of old activity records based on retention policies
- **Security Features**: IP tracking, user agent logging, and data retention policies

### Core Features
- `HasUserActivity` trait for automatic model activity tracking
- `UserActivityService` for advanced activity management
- `UserActivity` model with comprehensive activity data storage
- Artisan commands for maintenance and reporting
- Event system for custom activity handling
- Middleware for automatic IP and user agent tracking

### Configuration
- Comprehensive configuration file with environment variables
- Flexible model exclusion system
- Customizable field tracking
- Configurable retention policies
- Dashboard widget settings
- Route and API endpoint configuration

### Database
- `user_activities` table with optimized indexes
- JSON columns for flexible property and metadata storage
- Soft deletes support for activity records
- Efficient querying with proper indexing

### API Endpoints
- `GET /api/user-history/activities` - Get activities with filtering
- `GET /api/user-history/stats` - Get activity statistics
- `GET /api/user-history/chart-data` - Get chart data for analytics
- `GET /api/user-history/search` - Search activities

### Web Routes
- `GET /admin/user-history` - Dashboard view
- `GET /admin/user-history/activities` - Activity listing
- `GET /admin/user-history/stats` - Statistics view
- `GET /admin/user-history/users` - User listing
- `GET /admin/user-history/users/{user}` - User profile
- `GET /admin/user-history/reports/export` - Export reports

### Artisan Commands
- `user-history:cleanup` - Clean old activities based on retention policy
- `user-history:export` - Export activities to various formats
- `user-history:report` - Generate activity reports

### Events
- `ActivityRecorded` - Dispatched when a new activity is recorded
- `ActivityCleaned` - Dispatched when old activities are cleaned up

### Requirements
- PHP 8.1 or higher
- Laravel 10.x, 11.x, or 12.x
- spatie/laravel-permission 5.x or 6.x

### Dependencies
- Laravel Framework (^10.0|^11.0|^12.0)
- Spatie Laravel Permission (^5.0|^6.0)
- Orchestra Testbench for testing (^8.0|^9.0|^10.0)
- PHPUnit for testing (^10.0)
- Pest for testing (^2.0)

---

## Version History

- **1.0.0** - Initial release with comprehensive activity tracking and analytics features

## Support

For support, please open an issue on GitHub or contact the LaravelPlus team at team@laravelplus.com.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE). 