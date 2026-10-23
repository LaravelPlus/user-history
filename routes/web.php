<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\UserHistory\Http\Controllers\UserActivityController;

Route::group([
    'prefix' => config('user-history.routes.prefix'),
    'middleware' => [...config('user-history.routes.middleware', []), 'user-history.authorize'],
    'as' => 'user-history.',
], function (): void {

    // Dashboard routes
    Route::get('/', [UserActivityController::class, 'index'])->name('index');
    Route::get('/dashboard', [UserActivityController::class, 'dashboard'])->name('dashboard');

    // Activity listing and filtering
    Route::get('/activities', [UserActivityController::class, 'activities'])->name('activities');
    Route::get('/activities/filter', [UserActivityController::class, 'filter'])->name('activities.filter');

    // Users
    Route::get('/users', [UserActivityController::class, 'users'])->name('users');
    Route::get('/users/{userId}', [UserActivityController::class, 'userProfile'])->name('users.profile');

    // Statistics and reports
    Route::get('/stats', [UserActivityController::class, 'stats'])->name('stats');
    Route::get('/reports/export', [UserActivityController::class, 'export'])->name('reports.export');

    // Settings
    Route::get('/settings', [UserActivityController::class, 'settings'])->name('settings');
    Route::post('/settings', [UserActivityController::class, 'updateSettings'])->name('settings.update');

    // Cleanup
    Route::post('/cleanup', [UserActivityController::class, 'cleanup'])->name('cleanup');

    // API endpoints for AJAX requests
    Route::group(['prefix' => 'api', 'as' => 'api.'], function (): void {
        Route::get('/activities', [UserActivityController::class, 'apiActivities'])->name('activities');
        Route::get('/stats', [UserActivityController::class, 'apiStats'])->name('stats');
        Route::get('/chart-data', [UserActivityController::class, 'chartData'])->name('chart-data');
        Route::get('/timeline-chart', [UserActivityController::class, 'timelineChartData'])->name('timeline-chart');
        Route::get('/distribution-chart', [UserActivityController::class, 'distributionChartData'])->name('distribution-chart');
        Route::get('/search', [UserActivityController::class, 'apiSearch'])->name('search');
    });
});
