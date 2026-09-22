<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemAnalyticsController extends Controller
{
    public function index()
    {
        // 1. Infrastructure & Storage
        $diskTotal = disk_total_space(base_path());
        $diskFree = disk_free_space(base_path());$diskUsed = $diskTotal -$diskFree;
        $diskUsagePercent =$diskTotal > 0 ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

        // Database Size in MB
        $dbSizeResult = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE()");
        $databaseSizeMb =$dbSizeResult[0]->size_mb ?? 0;

        // 2. Error Tracking & Failed Jobs
        $failedJobsCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        // 3. Slowest Routes (Assuming you log request times in an analytics table)
        // If you don't have a performance log table yet, you can return sample or empty data
        $slowestRoutes = []; 

        // 4. Client Demographics (Browsers/Devices from your traffic logs)
        $browsers = DB::table('page_views') // Example analytics table
            ->select('browser', DB::raw('count(*) as total'))
            ->groupBy('browser')
            ->get();

        return response()->json([
            'server' => [
                'disk_usage_percent' => $diskUsagePercent,
                'disk_free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                'disk_total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),                 'db_size_mb' =>$databaseSizeMb,
                'php_version' => phpversion(),
            ],
            'errors' => [
                'failed_jobs' => $failedJobsCount,
            ],
            'demographics' => $browsers
        ]);
    }
}
