<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SystemAnalyticsController extends Controller
{
    public function index()
    {
        // 1. PHP Version
        $phpVersion = phpversion(); // e.g. "8.2.33"

        // 2. Hostinger Disk Usage
        $hostingerPlanLimitGb = 100; 
        $totalBytesLimit = $hostingerPlanLimitGb * 1024 * 1024 * 1024;

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

        // Database Size
        $databaseSizeMb = 0;
        try {
            $dbSizeResult = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE()");
            $databaseSizeMb = $dbSizeResult[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            $databaseSizeMb = 0;
        }

        $totalUsedBytes = $usedBytes + ($databaseSizeMb * 1024 * 1024);
        $diskUsagePercent = min(100, round(($totalUsedBytes / $totalBytesLimit) * 100, 2));

        $usedFormatted = $totalUsedBytes >= 1073741824 
            ? round($totalUsedBytes / 1024 / 1024 / 1024, 2) . ' GB' 
            : round($totalUsedBytes / 1024 / 1024, 2) . ' MB';

        // 3. Failed Jobs Count
        $failedJobsCount = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : 0;

        // 4. Browser Demographics
        $browsers = [];
        if (Schema::hasTable('page_views') && Schema::hasColumn('page_views', 'browser')) {
            $browsers = DB::table('page_views')
                ->select('browser', DB::raw('count(*) as total'))
                ->groupBy('browser')
                ->get();
        }

        // 5. Core Record Counts
        $totalUsers = Schema::hasTable('users') ? DB::table('users')->count() : 0;
        $totalPageViews = Schema::hasTable('page_views') ? DB::table('page_views')->count() : 0;
        
        // Active sessions in the last 15 minutes
        $activeSessions = 0;
        if (Schema::hasTable('sessions')) {
            $fifteenMinsAgo = Carbon::now()->subMinutes(15)->timestamp;
            $activeSessions = DB::table('sessions')->where('last_activity', '>=', $fifteenMinsAgo)->count();
        }

        // 6. Top 5 Visited Pages
        $topPages = [];
        if (Schema::hasTable('page_views')) {
            $topPages = DB::table('page_views')
                ->select('url', DB::raw('count(*) as total_views'))
                ->groupBy('url')
                ->orderByDesc('total_views')
                ->limit(5)
                ->get();
        }

        $auditLogs = [];
        if (Schema::hasTable('admin_activity_logs')) {
            $auditLogs = DB::table('admin_activity_logs')
                ->join('users', 'admin_activity_logs.admin_id', '=', 'users.id')
                ->select('admin_activity_logs.*', 'users.name as admin_name', 'users.email as admin_email')
                ->orderByDesc('admin_activity_logs.created_at')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'server' => [
                'disk_usage_percent' => $diskUsagePercent,
                'disk_used_text' => $usedFormatted,
                'disk_total_text' => $hostingerPlanLimitGb . ' GB',
                'db_size_mb' => $databaseSizeMb,
                'php_version' => $phpVersion,
            ],
            'records' => [
                'total_users' => $totalUsers,
                'active_sessions' => $activeSessions,
                'total_page_views' => $totalPageViews,
            ],
            'top_pages' => $topPages,
            'errors' => [
                'failed_jobs' => $failedJobsCount,
            ],
            'demographics' => $browsers,
            'audit_logs' => $auditLogs
        ]);
    }
}