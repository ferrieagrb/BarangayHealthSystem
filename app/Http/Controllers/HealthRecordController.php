<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthRecord;
use App\Models\Vaccination;
use App\Models\citizens;
use Illuminate\Support\Facades\Auth;

class HealthRecordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STORE HEALTH RECORD
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'diagnosis' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        HealthRecord::create([
            'citizen_id' => $request->citizen_id,
            'diagnosis' => $request->diagnosis,
            'notes' => $request->notes,
        ]);

        return redirect()->back()
            ->with('success', 'Health record added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW CITIZEN HEALTH RECORDS
    |--------------------------------------------------------------------------
    */

    public function show($id)
{
    if (!Auth::check() || Auth::user()->role !== 'bhw') abort(403);

    // Load citizen with health records and vaccinations
    $citizen = citizens::with([
    'healthRecords' => fn($q) => $q->latest(),
    'vaccinations' => fn($q) => $q->latest()
])->findOrFail($id);

    return view('bhw.citizen_show', compact('citizen'));
}

public function storeVaccination(Request $request)
{
    $request->validate([
        'citizen_id' => 'required|exists:citizens,id',
        'vaccine_name' => 'required|string|max:255',
        'date_administered' => 'required|date',
        'notes' => 'nullable|string',
    ]);

    Vaccination::create($request->all());

    return redirect()->back()->with('success', 'Vaccination added successfully.');
}

public function index(Request $request)
{
    $search = $request->input('search');

    $citizens = citizens::with('healthRecords')
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('Citizen_FName', 'like', "%{$search}%")
                  ->orWhere('Citizen_LName', 'like', "%{$search}%")
                  ->orWhereHas('healthRecords', function ($qr) use ($search) {
                      $qr->where('diagnosis', 'like', "%{$search}%");
                  });
            });
        })
        ->latest()
        ->paginate(10); // pagination

    return view('bhw.healthrecords', compact('citizens'));
}
}