<?php

namespace LaravelPlus\UserHistory\Providers;

use Illuminate\Support\ServiceProvider;
use LaravelPlus\UserHistory\Services\UserActivityService;
use LaravelPlus\UserHistory\Console\Commands\CleanUserActivitiesCommand;
use LaravelPlus\UserHistory\Console\Commands\ExportUserActivitiesCommand;

class UserHistoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/user-history.php', 'user-history'
        );

        $this->app->singleton(UserActivityService::class, function ($app) {
            return new UserActivityService();
        });

        $this->app->alias(UserActivityService::class, 'user-activity');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register features
        foreach (config('user-history.features', []) as $featureClass) {
            if (class_exists($featureClass) && method_exists($featureClass, 'register')) {
                $featureClass::register();
            }
        }

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/user-history.php' => config_path('user-history.php'),
        ], 'user-history-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations'),
        ], 'user-history-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/user-history'),
        ], 'user-history-views');

        // Publish language files
        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/user-history'),
        ], 'user-history-lang');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'user-history');

        // Load language files
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'user-history');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanUserActivitiesCommand::class,
                ExportUserActivitiesCommand::class,
            ]);
        }

        // Register routes
        $this->registerRoutes();

        // Register middleware
        $this->registerMiddleware();
    }

    /**
     * Register the package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('user-history.routes.enabled')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        if (config('user-history.api.enabled')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }
    }

    /**
     * Register the package middleware.
     */
    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('track.user.activity', \LaravelPlus\UserHistory\Middleware\TrackUserActivity::class);

        // For Laravel 11, we need to register middleware in bootstrap/app.php
        // The middleware registration is handled by the application's withMiddleware callback
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            UserActivityService::class,
            'user-activity',
        ];
    }
} 