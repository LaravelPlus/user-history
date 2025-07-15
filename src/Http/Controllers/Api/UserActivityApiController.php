<?php

namespace LaravelPlus\UserHistory\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use LaravelPlus\UserHistory\Services\UserActivityService;
use LaravelPlus\UserHistory\Models\UserActivity;
use LaravelPlus\UserHistory\Http\Resources\UserActivityResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserActivityApiController extends Controller
{
    public function __construct(
        protected UserActivityService $activityService
    ) {}

    /**
     * Get paginated activities.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to', 'search']);
        $perPage = $request->get('per_page', config('user-history.per_page', 15));
        
        $activities = $this->activityService->getPaginatedActivities($filters, $perPage);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Get a specific activity.
     */
    public function show(UserActivity $activity): UserActivityResource
    {
        $activity->load(['user', 'subject']);
        
        return new UserActivityResource($activity);
    }

    /**
     * Get activities for a specific user.
     */
    public function userActivities(Request $request, $user): AnonymousResourceCollection
    {
        $filters = array_merge($request->only(['action', 'date_from', 'date_to']), ['user_id' => $user->id]);
        $perPage = $request->get('per_page', config('user-history.per_page', 15));
        
        $activities = $this->activityService->getPaginatedActivities($filters, $perPage);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Get activities for a specific model.
     */
    public function modelActivities(Request $request, string $modelType, $modelId = null): AnonymousResourceCollection
    {
        $limit = $request->get('limit', 50);
        $activities = $this->activityService->getModelActivities($modelType, $modelId, $limit);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Get activity statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $stats = $this->activityService->getActivityStats($filters);
        
        return response()->json($stats);
    }

    /**
     * Get activities grouped by date for charts.
     */
    public function activitiesByDate(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $days = $request->get('days', 30);
        
        $activitiesByDate = $this->activityService->getActivitiesByDate($filters, $days);
        
        return response()->json($activitiesByDate);
    }

    /**
     * Get activities grouped by action for charts.
     */
    public function activitiesByAction(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'date_from', 'date_to']);
        
        $activitiesByAction = UserActivity::selectRaw('action, COUNT(*) as count')
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                $query->forUser($filters['user_id']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $dateFrom = $filters['date_from'] ?? now()->subDays(30);
                $dateTo = $filters['date_to'] ?? now();
                $query->dateRange($dateFrom, $dateTo);
            })
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
        
        return response()->json($activitiesByAction);
    }

    /**
     * Get activities grouped by user for charts.
     */
    public function activitiesByUser(Request $request): JsonResponse
    {
        $filters = $request->only(['action', 'date_from', 'date_to']);
        $limit = $request->get('limit', 10);
        
        $activitiesByUser = UserActivity::selectRaw('user_id, COUNT(*) as count')
            ->with('user:id,name,email')
            ->when(isset($filters['action']), function ($query) use ($filters) {
                $query->action($filters['action']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $dateFrom = $filters['date_from'] ?? now()->subDays(30);
                $dateTo = $filters['date_to'] ?? now();
                $query->dateRange($dateFrom, $dateTo);
            })
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get();
        
        return response()->json($activitiesByUser);
    }

    /**
     * Export activities to CSV.
     */
    public function exportCsv(Request $request)
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to']);
        $filepath = $this->activityService->exportToCsv($filters);
        
        return response()->download($filepath)->deleteFileAfterSend();
    }

    /**
     * Export activities to JSON.
     */
    public function exportJson(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'subject_type', 'subject_id', 'date_from', 'date_to']);
        $limit = $request->get('limit', config('user-history.export.max_records', 10000));
        
        $activities = $this->activityService->getActivities($filters)->limit($limit)->get();
        
        return response()->json(UserActivityResource::collection($activities));
    }

    /**
     * Search activities.
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $search = $request->get('q');
        $filters = array_merge($request->only(['user_id', 'action', 'date_from', 'date_to']), ['search' => $search]);
        $perPage = $request->get('per_page', config('user-history.per_page', 15));
        
        $activities = $this->activityService->getPaginatedActivities($filters, $perPage);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Filter activities.
     */
    public function filter(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', config('user-history.per_page', 15));
        
        $activities = $this->activityService->getPaginatedActivities($filters, $perPage);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Get dashboard data.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $stats = $this->activityService->getActivityStats();
        $recentActivities = $this->activityService->getDashboardActivities();
        
        return response()->json([
            'stats' => $stats,
            'recent_activities' => UserActivityResource::collection($recentActivities),
        ]);
    }

    /**
     * Get recent activities.
     */
    public function recent(Request $request): AnonymousResourceCollection
    {
        $limit = $request->get('limit', 10);
        $activities = $this->activityService->getDashboardActivities($limit);
        
        return UserActivityResource::collection($activities);
    }

    /**
     * Get activity summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to']);
        $stats = $this->activityService->getActivityStats($filters);
        
        // Get top users
        $topUsers = UserActivity::selectRaw('user_id, COUNT(*) as count')
            ->with('user:id,name,email')
            ->when(isset($filters['user_id']), function ($query) use ($filters) {
                $query->forUser($filters['user_id']);
            })
            ->when(isset($filters['action']), function ($query) use ($filters) {
                $query->action($filters['action']);
            })
            ->when(isset($filters['date_from']) || isset($filters['date_to']), function ($query) use ($filters) {
                $dateFrom = $filters['date_from'] ?? now()->subDays(30);
                $dateTo = $filters['date_to'] ?? now();
                $query->dateRange($dateFrom, $dateTo);
            })
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'stats' => $stats,
            'top_users' => $topUsers,
        ]);
    }

    /**
     * Quick search activities by query.
     */
    public function quickSearch(Request $request): JsonResponse
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