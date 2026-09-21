<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebsitePerformanceController extends Controller
{
    public function getPerformanceData()
    {
        // Safety check: if you don't have a 'page_views' table yet, 
        // this returns dummy data so your chart won't break with a 500 error!
        if (!DB::getSchemaBuilder()->hasTable('page_views')) {
            return response()->json([
                ['date' => '2026-09-01', 'views' => 10],
                ['date' => '2026-09-02', 'views' => 25],
                ['date' => '2026-09-03', 'views' => 18],
            ]);
        }

        $data = DB::table('page_views')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as views'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($data);
    }
}