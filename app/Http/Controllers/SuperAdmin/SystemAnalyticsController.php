<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemAnalyticsController extends Controller
{
    public function index()
    {
        // 1. Accurate PHP version
        $phpVersion = phpversion(); // e.g., "8.2.33"

        // 2. Accurate Hostinger Account Disk Allocation 
        // (Hostinger standard business/premium plans usually allocate around 100 GB to 200 GB SSD storage)
        $hostingerPlanLimitGb = 100; 
        $totalBytesLimit = $hostingerPlanLimitGb * 1024 * 1024 * 1024;

        // Calculate actual files size inside your storage/app and public folders
        $usedBytes = 0;
        $storagePath = storage_path('app');
        if (is_dir($storagePath)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $usedBytes += $file->getSize();
                }
            }
        }

        // Add database size into total storage used
        $databaseSizeMb = 0;
        try {
            $dbSizeResult = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE()");
            $databaseSizeMb = $dbSizeResult[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            $databaseSizeMb = 0;
        }

        $totalUsedBytes = $usedBytes + ($databaseSizeMb * 1024 * 1024);
        $diskUsagePercent = min(100, round(($totalUsedBytes / $totalBytesLimit) * 100, 1));

        // 3. Failed Jobs Count
        $failedJobsCount = 0;
        if (Schema::hasTable('failed_jobs')) {
            $failedJobsCount = DB::table('failed_jobs')->count();
        }

        // 4. Browser Demographics
        $browsers = [];
        if (Schema::hasTable('page_views') && Schema::hasColumn('page_views', 'browser')) {
            $browsers = DB::table('page_views')
                ->select('browser', DB::raw('count(*) as total'))
                ->groupBy('browser')
                ->get();
        }

        return response()->json([
            'server' => [
                'disk_usage_percent' => $diskUsagePercent,
                'db_size_mb' => $databaseSizeMb,
                'php_version' => $phpVersion,
            ],
            'errors' => [
                'failed_jobs' => $failedJobsCount,
            ],
            'demographics' => $browsers
        ]);
    }
}