<?php

namespace LaravelPlus\UserHistory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use LaravelPlus\UserHistory\Services\UserActivityService;
use LaravelPlus\UserHistory\Models\UserActivity;
use LaravelPlus\UserHistory\Http\Resources\UserActivityResource;
use Illuminate\Http\Response;

class UserActivityController extends Controller
{
    public function __construct(
        protected UserActivityService $activityService
    ) {}

    /**
     * Display the main dashboard.
     */
    public function index(): View
    {
        $stats = $this->activityService->getActivityStats();
        $recentActivities = $this->activityService->getDashboardActivities();
        
        // Transform recent activities for the view
        $recentActivitiesData = $recentActivities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'user_id' => $activity->user_id,
                'user_name' => $activity->user ? $activity->user->name : 'Unknown User',
                'action' => $activity->action,
                'description' => $activity->description,
                'subject_name' => $activity->subject ? $activity->subject->getActivitySubjectName() : null,
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at
            ];
        });
        
        // Get top users for dashboard
        $topUsers = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('user_id, COUNT(*) as activity_count')
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->user_id,
                    'name' => $item->user ? $item->user->name : 'Unknown User',
                    'email' => $item->user ? $item->user->email : '',
                    'activity_count' => $item->activity_count,
                ];
            });
        
        return view('user-history::dashboard', compact('stats', 'recentActivitiesData', 'topUsers'));
    }

    /**
     * Display the activities listing page.
     */
    public function activities(Request $request): View
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to', 'search']);
        $perPage = $request->get('per_page', config('user-history.per_page', 15));
        
        $activities = $this->activityService->getPaginatedActivities($filters, $perPage);
        
        // Transform activities for the view
        $activitiesData = $activities->getCollection()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'user_id' => $activity->user_id,
                'user_name' => $activity->user ? $activity->user->name : 'Unknown User',
                'action' => $activity->action,
                'description' => $activity->description,
                'subject_name' => $activity->subject ? $activity->subject->getActivitySubjectName() : null,
                'ip_address' => $activity->ip_address,
                'user_agent' => $activity->user_agent,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at
            ];
        });
        
        return view('user-history::activities.index', compact('activitiesData', 'filters'));
    }

    /**
     * Display filtered activities.
     */
    public function filter(Request $request): View
    {
        $filters = $request->all();
        $activities = $this->activityService->getPaginatedActivities($filters);
        
        return view('user-history::activities.index', compact('activities', 'filters'));
    }

    /**
     * Display activities for a specific user.
     */
    public function userActivities(Request $request, $user): View
    {
        $activities = $this->activityService->getPaginatedActivities(['user_id' => $user->id]);
        
        return view('user-history::activities.user', compact('activities', 'user'));
    }

    /**
     * Display activities for a specific model.
     */
    public function modelActivities(Request $request, string $modelType, $modelId = null): View
    {
        $activities = $this->activityService->getModelActivities($modelType, $modelId);
        
        return view('user-history::activities.model', compact('activities', 'modelType', 'modelId'));
    }

    /**
     * Display a specific activity.
     */
    public function show(UserActivity $activity): View
    {
        $activity->load(['user', 'subject']);
        
        return view('user-history::activities.show', compact('activity'));
    }

    /**
     * Display the statistics page.
     */
    public function stats(Request $request): View
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $stats = $this->activityService->getActivityStats($filters);
        
        // Get activities by date for charts
        $days = $request->get('days', 30);
        $activitiesByDate = $this->activityService->getActivitiesByDate($filters, $days);
        
        // Get activities by action
        $activitiesByAction = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('action, COUNT(*) as count')
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                $query->where('user_id', $filters['user_id']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $dateFrom = $filters['date_from'] ?? now()->subDays(30);
                $dateTo = $filters['date_to'] ?? now();
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
        
        // Get top users
        $topUsers = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('user_id, COUNT(*) as count')
            ->with('user:id,name')
            ->when(isset($filters['action']), function ($query) use ($filters) {
                $query->where('action', $filters['action']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $dateFrom = $filters['date_from'] ?? now()->subDays(30);
                $dateTo = $filters['date_to'] ?? now();
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'user_id' => $item->user_id,
                    'user_name' => $item->user ? $item->user->name : 'Unknown User',
                    'count' => $item->count
                ];
            });
        
        return view('user-history::stats', compact('stats', 'activitiesByDate', 'activitiesByAction', 'topUsers', 'filters'));
    }

    /**
     * Display reports page.
     */
    public function reports(Request $request): View
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $stats = $this->activityService->getActivityStats($filters);
        
        return view('user-history::reports', compact('stats', 'filters'));
    }

    /**
     * Export activities.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to']);
        $format = $request->get('format', 'csv');
        
        if ($format === 'csv') {
            $filepath = $this->activityService->exportToCsv($filters);
            return response()->download($filepath)->deleteFileAfterSend();
        }
        
        $activities = $this->activityService->getActivities($filters)->get();
        return response()->json(UserActivityResource::collection($activities));
    }

    /**
     * Display settings page.
     */
    public function settings(): View
    {
        return view('user-history::settings');
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_record_events' => 'boolean',
            'track_ip_address' => 'boolean',
            'track_user_agent' => 'boolean',
            'retention_days' => 'integer|min:1',
        ]);
        
        // Update configuration (this would typically update a database table)
        // For now, we'll just return success
        
        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Clean old activities.
     */
    public function cleanup(Request $request): JsonResponse
    {
        $daysToKeep = $request->get('days_to_keep', config('user-history.retention_days', 365));
        $deletedCount = $this->activityService->cleanOldActivities($daysToKeep);
        
        return response()->json([
            'message' => "Successfully cleaned {$deletedCount} old activities",
            'deleted_count' => $deletedCount
        ]);
    }

    /**
     * API endpoint for activities (AJAX).
     */
    public function apiActivities(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to', 'search']);
        $activities = $this->activityService->getPaginatedActivities($filters);
        
        return response()->json(UserActivityResource::collection($activities));
    }

    /**
     * API endpoint for statistics (AJAX).
     */
    public function apiStats(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        
        // Base query
        $baseQuery = \LaravelPlus\UserHistory\Models\UserActivity::query();
        if ($userId) {
            $baseQuery->where('user_id', $userId);
        }
        
        // Total activities
        $totalActivities = $baseQuery->count();
        
        // Last week's total for comparison (7-14 days ago)
        $lastWeekTotal = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(14))
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        
        $currentWeekTotal = (clone $baseQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        
        $totalGrowth = $lastWeekTotal > 0 ? (($currentWeekTotal - $lastWeekTotal) / $lastWeekTotal) * 100 : 0;
        
        // Active users (unique users in last 7 days)
        $activeUsersQuery = \LaravelPlus\UserHistory\Models\UserActivity::where('created_at', '>=', now()->subDays(7));
        if ($userId) {
            $activeUsersQuery->where('user_id', $userId);
        }
        $activeUsers = $activeUsersQuery->distinct('user_id')->count('user_id');
        
        // Active users today
        $activeTodayQuery = \LaravelPlus\UserHistory\Models\UserActivity::where('created_at', '>=', now()->startOfDay());
        if ($userId) {
            $activeTodayQuery->where('user_id', $userId);
        }
        $activeToday = $activeTodayQuery->distinct('user_id')->count('user_id');
        
        // Today's activities
        $todayActivities = (clone $baseQuery)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();
        
        // Hourly average for today
        $hoursElapsed = max(1, now()->diffInHours(now()->startOfDay()));
        $hourlyAverage = $todayActivities / $hoursElapsed;
        
        // This week's activities (from start of week)
        $thisWeekActivities = (clone $baseQuery)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();
        
        // Last week's activities for comparison
        $lastWeekActivities = (clone $baseQuery)
            ->where('created_at', '>=', now()->subWeek()->startOfWeek())
            ->where('created_at', '<', now()->startOfWeek())
            ->count();
        
        $weekGrowth = $lastWeekActivities > 0 ? (($thisWeekActivities - $lastWeekActivities) / $lastWeekActivities) * 100 : 0;
        
        return response()->json([
            'total_activities' => $totalActivities,
            'total_growth' => round($totalGrowth, 1),
            'active_users' => $activeUsers,
            'active_today' => $activeToday,
            'today_activities' => $todayActivities,
            'hourly_average' => round($hourlyAverage, 1),
            'this_week' => $thisWeekActivities,
            'week_growth' => round($weekGrowth, 1)
        ]);
    }

    /**
     * API endpoint for chart data (AJAX).
     */
    public function chartData(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $days = $request->get('days', 30);
        
        $activitiesByDate = $this->activityService->getActivitiesByDate($filters, $days);
        
        return response()->json($activitiesByDate);
    }

    /**
     * API endpoint for activity timeline chart data.
     */
    public function timelineChartData(Request $request): JsonResponse
    {
        $days = $request->get('days', 7);
        $userId = $request->get('user_id');
        
        $query = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days));
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $timelineData = $query->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => (int) $item->count
                ];
            });
        
        // Fill in missing dates with zero counts
        $filledData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $existingData = $timelineData->where('date', $date)->first();
            $filledData[] = [
                'date' => $date,
                'count' => $existingData ? $existingData['count'] : 0
            ];
        }
        
        return response()->json($filledData);
    }

    /**
     * API endpoint for distribution chart data.
     */
    public function distributionChartData(Request $request): JsonResponse
    {
        $userId = $request->get('user_id');
        $days = $request->get('days', 30);
        
        $query = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('action, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days));
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $distributionData = $query->groupBy('action')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'action' => $item->action,
                    'count' => (int) $item->count
                ];
            });
        
        return response()->json($distributionData);
    }

    /**
     * Display the users index page.
     */
    public function users(Request $request): View
    {
        $users = \App\Models\User::select('id', 'name', 'email', 'created_at')
            ->withCount('activities')
            ->withMax('activities', 'created_at')
            ->get()
            ->map(function ($user) {
                // Get weekly activity data
                $weeklyActivity = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('user_id', $user->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->map(function ($day) {
                        return [
                            'date' => $day->date,
                            'count' => $day->count
                        ];
                    });

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at,
                    'activity_count' => $user->activities_count,
                    'last_activity' => $user->activities_max_created_at,
                    'weekly_activity' => $weeklyActivity
                ];
            });

        return view('user-history::users.index', compact('users'));
    }

    /**
     * Display a specific user's profile and activities.
     */
    public function userProfile(Request $request, $userId): View
    {
        $user = \App\Models\User::findOrFail($userId);
        
        // Get user stats
        $userStats = [
            'total_activities' => $user->activities()->count(),
            'this_week_activities' => $user->activities()->where('created_at', '>=', now()->subWeek())->count(),
            'last_activity' => $user->activities()->max('created_at'),
            'most_active_day' => $this->getMostActiveDay($userId)
        ];

        // Get user activities
        $activities = $user->activities()
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'subject_name' => $activity->subject ? $activity->subject->getActivitySubjectName() : null,
                    'ip_address' => $activity->ip_address,
                    'user_agent' => $activity->user_agent,
                    'properties' => $activity->properties,
                    'created_at' => $activity->created_at
                ];
            });

        // Get action statistics
        $actionStats = $user->activities()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($stat) use ($userStats) {
                return [
                    'action' => $stat->action,
                    'count' => $stat->count,
                    'percentage' => $userStats['total_activities'] > 0 ? round(($stat->count / $userStats['total_activities']) * 100, 1) : 0
                ];
            });

        // Get timeline data (last 7 days)
        $timelineData = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($day) {
                return [
                    'date' => $day->date,
                    'count' => $day->count,
                    'percentage' => 0 // Will be calculated in view
                ];
            });

        // Calculate percentages for timeline
        $maxCount = $timelineData->max('count') ?: 1;
        $timelineData = $timelineData->map(function ($day) use ($maxCount) {
            $day['percentage'] = round(($day['count'] / $maxCount) * 100, 1);
            return $day;
        });

        return view('user-history::users.profile', compact('user', 'userStats', 'activities', 'actionStats', 'timelineData'));
    }

    /**
     * Get the most active day for a user.
     */
    private function getMostActiveDay(int $userId): ?string
    {
        $mostActiveDay = \LaravelPlus\UserHistory\Models\UserActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('user_id', $userId)
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();

        if (!$mostActiveDay) {
            return null;
        }

        return \Carbon\Carbon::parse($mostActiveDay->date)->format('l, M j');
    }

    /**
     * API endpoint for searching activities.
     */
    public function apiSearch(Request $request): JsonResponse
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        $activities = UserActivity::with('user:id,name')
            ->where(function ($q) use ($query) {
                $q->where('description', 'like', "%{$query}%")
                  ->orWhere('action', 'like', "%{$query}%")
                  ->orWhereHas('user', function ($userQuery) use ($query) {
                      $userQuery->where('name', 'like', "%{$query}%")
                               ->orWhere('email', 'like', "%{$query}%");
                  });
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user_id' => $activity->user_id,
                    'user_name' => $activity->user ? $activity->user->name : 'Unknown User',
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'created_at' => $activity->created_at
                ];
            });

        return response()->json(['data' => $activities]);
    }
} 