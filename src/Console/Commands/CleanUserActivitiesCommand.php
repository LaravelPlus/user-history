<?php

namespace LaravelPlus\UserHistory\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\UserHistory\Services\UserActivityService;

class CleanUserActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user-history:clean 
                            {--days= : Number of days to keep activities (defaults to config value)}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     */
    protected $description = 'Clean old user activities based on retention settings';

    public function __construct(
        protected UserActivityService $activityService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysToKeep = $this->option('days') ?? config('user-history.retention_days', 365);
        $dryRun = $this->option('dry-run');

        $this->info("Cleaning user activities older than {$daysToKeep} days...");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No activities will be deleted');
            
            // Count activities that would be deleted
            $cutoffDate = now()->subDays($daysToKeep);
            $count = \LaravelPlus\UserHistory\Models\UserActivity::where('created_at', '<', $cutoffDate)->count();
            
            $this->info("Would delete {$count} activities older than {$cutoffDate->format('Y-m-d H:i:s')}");
            
            if ($count > 0) {
                $this->table(
                    ['Date Range', 'Count'],
                    [
                        ['Older than ' . $cutoffDate->format('Y-m-d'), $count]
                    ]
                );
            }
            
            return self::SUCCESS;
        }

        $deletedCount = $this->activityService->cleanOldActivities($daysToKeep);

        if ($deletedCount > 0) {
            $this->info("Successfully cleaned {$deletedCount} old activities");
        } else {
            $this->info('No old activities found to clean');
        }

        return self::SUCCESS;
    }
} 