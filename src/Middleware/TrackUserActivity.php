<?php

declare(strict_types=1);

namespace LaravelPlus\UserHistory\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelPlus\UserHistory\Models\UserActivity;
use Symfony\Component\HttpFoundation\Response;

final class TrackUserActivity
{
    /**
     * Activity data to be logged.
     */
    private $activityData = null;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $action = null, ?string $description = null): Response
    {
        $response = $next($request);

        // Only track if user is authenticated and tracking is enabled
        if (!Auth::check() || !config('user-history.auto_record_events', true)) {
            return $response;
        }

        // Check if path should be excluded
        $excludedPaths = config('user-history.excluded_paths', []);
        $currentPath = $request->path();

        foreach ($excludedPaths as $excludedPath) {
            if (fnmatch($excludedPath, $currentPath)) {
                return $response;
            }
        }

        // Check tracking mode
        $trackingMode = config('user-history.page_visit_tracking', 'important');

        if ($trackingMode === 'important') {
            // Only track important paths
            $importantPaths = config('user-history.important_paths', []);
            $shouldTrack = false;

            foreach ($importantPaths as $importantPath) {
                if (fnmatch($importantPath, $currentPath)) {
                    $shouldTrack = true;
                    break;
                }
            }

            if (!$shouldTrack) {
                return $response;
            }
        } elseif ($trackingMode === 'minimal') {
            // Only track specific actions, not page visits
            return $response;
        }
        // 'all' mode tracks everything (except excluded paths)

        // Get action from parameter or request method
        $action = $action ?? $this->getActionFromRequest($request);

        // Get description from parameter or generate from request
        $description = $description ?? $this->getDescriptionFromRequest($request);

        // Check if action should be excluded
        if (in_array($action, config('user-history.excluded_actions', []))) {
            return $response;
        }

        // Prepare activity data
        $this->activityData = [
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => config('user-history.track_ip_address', true) ? $request->ip() : null,
            'user_agent' => config('user-history.track_user_agent', true) ? $request->userAgent() : null,
            'properties' => config('user-history.track_properties', true) ? [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'status_code' => $response->getStatusCode(),
            ] : null,
            'metadata' => [
                'middleware' => 'TrackUserActivity',
                'timestamp' => now()->toISOString(),
            ],
        ];

        // Use immediate execution if defer is disabled
        if (!config('user-history.global_middleware.defer', false)) {
            UserActivity::create($this->activityData);
            $this->activityData = null; // Clear after immediate execution
        }

        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // If we have activity data and defer is enabled, log it now
        if ($this->activityData && config('user-history.global_middleware.defer', false)) {
            UserActivity::create($this->activityData);
        }
    }

    /**
     * Get action from request method and route.
     */
    private function getActionFromRequest(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route()?->getName();

        // Map HTTP methods to actions
        $actionMap = [
            'GET' => 'viewed',
            'POST' => 'created',
            'PUT' => 'updated',
            'PATCH' => 'updated',
            'DELETE' => 'deleted',
        ];

        $action = $actionMap[$method] ?? 'accessed';

        // Enhance action based on route name
        if ($routeName) {
            if (str_contains($routeName, 'login')) {
                $action = 'login';
            } elseif (str_contains($routeName, 'logout')) {
                $action = 'logout';
            } elseif (str_contains($routeName, 'password')) {
                $action = 'password_changed';
            } elseif (str_contains($routeName, 'profile')) {
                $action = 'profile_updated';
            }
        }

        return $action;
    }

    /**
     * Get description from request.
     */
    private function getDescriptionFromRequest(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Generate description based on route and method
        if ($routeName) {
            return ucfirst(str_replace(['.', '-', '_'], ' ', $routeName));
        }

        // Fallback to path-based description
        $pathParts = explode('/', mb_trim($path, '/'));
        $lastPart = end($pathParts);

        if ($lastPart) {
            return ucfirst(str_replace(['-', '_'], ' ', $lastPart));
        }

        return ucfirst($method) . ' request';
    }
}
