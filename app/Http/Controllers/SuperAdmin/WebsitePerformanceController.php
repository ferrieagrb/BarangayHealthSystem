<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebsitePerformanceController extends Controller
{
    public function getPerformanceData()
    {
        // Fallback dummy data including average load time (ms)
        if (!DB::getSchemaBuilder()->hasTable('page_views')) {
            return response()->json([
                ['date' => '2026-09-01', 'views' => 10, 'avg_load_time' => 142],
                ['date' => '2026-09-02', 'views' => 25, 'avg_load_time' => 118],
                ['date' => '2026-09-03', 'views' => 18, 'avg_load_time' => 135],
            ]);
        }

        $startDate = Carbon::now()->subDays(30)->startOfDay();

        // Fetch daily views and average load time from the database
        $data = DB::table('page_views')
            ->select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('count(*) as views'),
                DB::raw('ROUND(AVG(load_time)) as avg_load_time')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }
}