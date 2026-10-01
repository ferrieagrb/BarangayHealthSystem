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
        $citizenLogs = CitizenActivityLog::latest()->paginate(20, ['*'], 'citizen_page');
        $supplyLogs = SupplyLog::latest()->paginate(20, ['*'], 'supply_page');
        $healthRecordLogs = HealthRecordActivityLog::latest()->paginate(20, ['*'], 'health_page');
        $vehicleLogs = VehicleLog::latest()->paginate(20, ['*'], 'vehicle_page');

        return view('bhw.logs', compact(
            'citizenLogs',
            'supplyLogs',
            'healthRecordLogs',
            'vehicleLogs'
        ));
    }
}