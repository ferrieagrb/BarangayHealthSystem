<?php

namespace App\Http\Controllers;

use App\Models\CitizenActivityLog;
use App\Models\SupplyLog;
use App\Models\HealthRecordActivityLog;
use App\Models\VehicleLog;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Referral;
use App\Models\MaintenanceRecord;
use App\Models\SupplyRelease;

class LogController extends Controller
{
    public function index()
    {
        $citizenLogs = CitizenActivityLog::with(['user', 'citizen'])->latest()->paginate(20, ['*'], 'citizen_page');
        $supplyLogs = SupplyLog::with(['user', 'supply', 'citizen'])->latest()->paginate(20, ['*'], 'supply_page');
        $healthRecordLogs = HealthRecordActivityLog::with('user')->latest()->paginate(20, ['*'], 'health_page');
        $vehicleLogs = VehicleLog::with('user')->latest()->paginate(20, ['*'], 'vehicle_page');
        $announcementLogs = Announcement::with('user')->latest()->paginate(20, ['*'], 'announcement_page');
        $eventLogs = Event::with('user')->latest()->paginate(20, ['*'], 'event_page');
        $referralLogs = Referral::with('user')->latest()->paginate(20, ['*'], 'referral_page');
        $maintenanceLogs = MaintenanceRecord::with(['citizen', 'user'])->latest()->paginate(20, ['*'], 'maintenance_page');
        $releaseLogs = SupplyRelease::with(['user', 'citizen', 'supply', 'event'])
            ->latest()
            ->paginate(20, ['*'], 'release_page');

        return view('bhw.logs', compact(
            'citizenLogs',
            'supplyLogs',
            'healthRecordLogs',
            'vehicleLogs',
            'announcementLogs',
            'eventLogs',
            'referralLogs',
            'maintenanceLogs',
            'releaseLogs'
        ));
    }
}