<?php

namespace LaravelPlus\UserHistory\Services;

use LaravelPlus\UserHistory\Models\UserActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class UserActivityService
{
    /**
     * Get activities with optional filters.
     */
    public function getActivities(array $filters = []): Builder
    {
        $query = UserActivity::with(['user', 'subject']);

        // Filter by user
        if (isset($filters['user_id'])) {
            $query->forUser($filters['user_id']);
        }

        // Filter by action
        if (isset($filters['action'])) {
            $query->action($filters['action']);
        }

        // Filter by subject
        if (isset($filters['subject_type'])) {
            $query->forSubject($filters['subject_type'], $filters['subject_id'] ?? null);
        }

        // Filter by date range
        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            $dateFrom = $filters['date_from'] ?? Carbon::now()->subDays(30);
            $dateTo = $filters['date_to'] ?? Carbon::now();
            $query->dateRange($dateFrom, $dateTo);
        }

        // Filter by IP address
        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        // Search in description
        if (isset($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get paginated activities.
     */
    public function getPaginatedActivities(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getActivities($filters)->paginate($perPage);
    }

    /**
     * Get recent activities for a user.
     */
    public function getRecentUserActivities(int $userId, int $limit = 10): Collection
    {
        return UserActivity::with(['user', 'subject'])
            ->forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get activities for a specific model.
     */
    public function getModelActivities(string $modelClass, int $modelId = null, int $limit = 50): Collection
    {
        $query = UserActivity::with(['user', 'subject'])
            ->forSubject($modelClass, $modelId)
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get activity statistics.
     */
    public function getActivityStats(array $filters = []): array
    {
        $query = $this->getActivities($filters);

        $totalActivities = $query->count();
        $uniqueUsers = $query->distinct('user_id')->count('user_id');
        $todayActivities = $query->whereDate('created_at', Carbon::today())->count();
        $thisWeekActivities = $query->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        // Get most common actions
        $topActions = $query->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->pluck('count', 'action')
            ->toArray();

        return [
            'total_activities' => $totalActivities,
            'unique_users' => $uniqueUsers,
            'today_activities' => $todayActivities,
            'this_week_activities' => $thisWeekActivities,
            'top_actions' => $topActions,
        ];
    }

    /**
     * Get activities grouped by date.
     */
    public function getActivitiesByDate(array $filters = [], int $days = 30): Collection
    {
        $startDate = Carbon::now()->subDays($days);

        return UserActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                $query->forUser($filters['user_id']);
            })
            ->when(isset($filters['action']), function ($query) use ($filters) {
                $query->action($filters['action']);
            })
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Clean old activities.
     */
    public function cleanOldActivities(int $daysToKeep = 365): int
    {
        $cutoffDate = Carbon::now()->subDays($daysToKeep);
        
        return UserActivity::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Export activities to CSV.
     */
    public function exportToCsv(array $filters = []): string
    {
        $activities = $this->getActivities($filters)->get();
        
        $filename = 'user_activities_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }
        
        $handle = fopen($filepath, 'w');
        
        // Write headers
        fputcsv($handle, [
            'ID',
            'User',
            'Action',
            'Description',
            'Subject Type',
            'Subject ID',
            'IP Address',
            'User Agent',
            'Created At',
        ]);
        
        // Write data
        foreach ($activities as $activity) {
            fputcsv($handle, [
                $activity->id,
                $activity->user ? $activity->user->name : 'Unknown',
                $activity->action,
                $activity->description,
                $activity->subject_type,
                $activity->subject_id,
                $activity->ip_address,
                $activity->user_agent,
                $activity->created_at->format('Y-m-d H:i:s'),
            ]);
        }
        
        fclose($handle);
        
        return $filepath;
    }

    /**
     * Get activities for dashboard widget.
     */
    public function getDashboardActivities(int $limit = 10): Collection
    {
        return UserActivity::with(['user', 'subject'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Record an activity with deferred execution (after response is sent).
     */
    public function recordDeferredActivity(array $data): void
    {
        // Use dispatch to run after response is sent
        dispatch(function () use ($data) {
            UserActivity::create($data);
        })->afterResponse();
    }

    /**
     * Record a simple activity with deferred execution.
     */
    public function recordDeferredSimpleActivity(string $action, string $description, ?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        
        if (!$userId) {
            return;
        }

        $this->recordDeferredActivity([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => config('user-history.track_ip_address', true) ? request()->ip() : null,
            'user_agent' => config('user-history.track_user_agent', true) ? request()->userAgent() : null,
            'properties' => config('user-history.track_properties', true) ? [
                'method' => request()->method(),
                'url' => request()->fullUrl(),
                'route' => request()->route()?->getName(),
            ] : null,
            'metadata' => [
                'source' => 'manual_deferred',
                'timestamp' => now()->toISOString(),
            ],
        ]);
    }
} 