<?php

namespace App\Http\Controllers;

use App\Models\CitizenActivityLog;
use App\Models\SupplyLog;
use App\Models\HealthRecordActivityLog;
use App\Models\VehicleLog;

class LogController extends Controller
{
    public function index()
    {
        $citizenLogs = CitizenActivityLog::latest()->get();
        $supplyLogs = SupplyLog::latest()->get();
        $healthRecordLogs = HealthRecordActivityLog::latest()->get();
        $vehicleLogs = VehicleLog::latest()->get();

        return view('bhw.logs', compact(
            'citizenLogs',
            'supplyLogs',
            'healthRecordLogs',
            'vehicleLogs'
        ));
    }
}