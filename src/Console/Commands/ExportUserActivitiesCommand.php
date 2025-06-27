<?php

namespace LaravelPlus\UserHistory\Console\Commands;

use Illuminate\Console\Command;
use LaravelPlus\UserHistory\Services\UserActivityService;
use Carbon\Carbon;

class ExportUserActivitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user-history:export 
                            {--format=csv : Export format (csv, json)}
                            {--user-id= : Filter by user ID}
                            {--action= : Filter by action}
                            {--date-from= : Filter from date (Y-m-d)}
                            {--date-to= : Filter to date (Y-m-d)}
                            {--output= : Output file path}
                            {--limit= : Maximum number of records to export}';

    /**
     * The console command description.
     */
    protected $description = 'Export user activities to CSV or JSON format';

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
        $format = $this->option('format');
        $output = $this->option('output');
        $limit = $this->option('limit');

        // Build filters
        $filters = [];
        
        if ($this->option('user-id')) {
            $filters['user_id'] = $this->option('user-id');
        }
        
        if ($this->option('action')) {
            $filters['action'] = $this->option('action');
        }
        
        if ($this->option('date-from')) {
            $filters['date_from'] = Carbon::parse($this->option('date-from'));
        }
        
        if ($this->option('date-to')) {
            $filters['date_to'] = Carbon::parse($this->option('date-to'));
        }

        $this->info("Exporting user activities in {$format} format...");

        if ($format === 'csv') {
            return $this->exportCsv($filters, $output);
        } elseif ($format === 'json') {
            return $this->exportJson($filters, $output, $limit);
        } else {
            $this->error("Unsupported format: {$format}. Supported formats: csv, json");
            return self::FAILURE;
        }
    }

    /**
     * Export to CSV format.
     */
    protected function exportCsv(array $filters, ?string $output): int
    {
        try {
            $filepath = $this->activityService->exportToCsv($filters);
            
            if ($output) {
                // Copy to specified output location
                copy($filepath, $output);
                unlink($filepath); // Remove temporary file
                $filepath = $output;
            }
            
            $this->info("Activities exported to: {$filepath}");
            
            // Show file info
            $fileSize = filesize($filepath);
            $this->info("File size: " . $this->formatBytes($fileSize));
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to export CSV: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Export to JSON format.
     */
    protected function exportJson(array $filters, ?string $output, ?int $limit): int
    {
        try {
            $activities = $this->activityService->getActivities($filters);
            
            if ($limit) {
                $activities = $activities->limit($limit);
            }
            
            $data = $activities->get();
            
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if ($output) {
                file_put_contents($output, $jsonData);
                $filepath = $output;
            } else {
                $filepath = storage_path('app/exports/user_activities_' . now()->format('Y-m-d_H-i-s') . '.json');
                file_put_contents($filepath, $jsonData);
            }
            
            $this->info("Activities exported to: {$filepath}");
            $this->info("Records exported: " . $data->count());
            
            // Show file info
            $fileSize = filesize($filepath);
            $this->info("File size: " . $this->formatBytes($fileSize));
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to export JSON: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Format bytes to human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 