<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleLog;


class VehicleLogController extends Controller
{
    public function store(Request $request)
{
    VehicleLog::create([
        'vehicle_name' => $request->vehicle_name,
        'borrower' => $request->borrower,
        'borrowed_at' => $request->borrowed_at,
    ]);

    return back()->with('success', 'Vehicle log added!');
}
}
