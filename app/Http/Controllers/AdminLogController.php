<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CitizenActivityLog;
use App\Models\SupplyLog;
use App\Models\HealthRecordActivityLog;

class AdminLogController extends Controller
{
    public function index()
    {
        $citizenLogs = CitizenActivityLog::latest()->get();
        $supplyLogs = SupplyLog::latest()->get();
        $healthRecordLogs = HealthRecordActivityLog::latest()->get();

        return view('admin.admin_logs', compact(
            'citizenLogs',
            'supplyLogs',
            'healthRecordLogs'
        ));
    }
}
