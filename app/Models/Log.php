<?php

namespace App\Http\Controllers;

use App\Models\CitizenActivityLog;
use App\Models\SupplyLog;
use App\Models\HealthRecordActivityLog;

class LogController extends Controller
{
    public function index()
    {
        $citizenLogs = CitizenActivityLog::latest()->get();
        $supplyLogs = SupplyLog::latest()->get();
        $healthRecordLogs = HealthRecordActivityLog::latest()->get();

        return view('logs.index', [
            'citizenLogs' => $citizenLogs,
            'supplyLogs' => $supplyLogs,
            'healthRecordLogs' => $healthRecordLogs,
        ]);
    }
}
