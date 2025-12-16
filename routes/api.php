<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaravelPlus\UserHistory\Http\Controllers\Api\UserActivityApiController;

Route::group([
    'prefix' => config('user-history.api.prefix'),
    'middleware' => config('user-history.api.middleware'),
    'as' => 'api.user-history.',
], function (): void {

    // Get all activities with pagination
    Route::get('/activities', [UserActivityApiController::class, 'index'])->name('activities.index');

    // Get specific activity
    Route::get('/activities/{activity}', [UserActivityApiController::class, 'show'])->name('activities.show');

    // Get user activities
    Route::get('/users/{user}/activities', [UserActivityApiController::class, 'userActivities'])->name('users.activities');

    // Get model activities
    Route::get('/models/{modelType}/{modelId?}', [UserActivityApiController::class, 'modelActivities'])->name('models.activities');

    // Get activity statistics
    Route::get('/stats', [UserActivityApiController::class, 'stats'])->name('stats');

    // Get chart data
    Route::get('/charts/activities-by-date', [UserActivityApiController::class, 'activitiesByDate'])->name('charts.activities-by-date');
    Route::get('/charts/activities-by-action', [UserActivityApiController::class, 'activitiesByAction'])->name('charts.activities-by-action');
    Route::get('/charts/activities-by-user', [UserActivityApiController::class, 'activitiesByUser'])->name('charts.activities-by-user');

    // Export activities
    Route::get('/export/csv', [UserActivityApiController::class, 'exportCsv'])->name('export.csv');
    Route::get('/export/json', [UserActivityApiController::class, 'exportJson'])->name('export.json');

    // Search activities
    Route::get('/search', [UserActivityApiController::class, 'search'])->name('search');

    // Filter activities
    Route::post('/filter', [UserActivityApiController::class, 'filter'])->name('filter');

    // Dashboard data
    Route::get('/dashboard', [UserActivityApiController::class, 'dashboard'])->name('dashboard');

    // Recent activities
    Route::get('/recent', [UserActivityApiController::class, 'recent'])->name('recent');

    // Activity summary
    Route::get('/summary', [UserActivityApiController::class, 'summary'])->name('summary');
});
