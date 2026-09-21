<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthRecord;
use App\Models\Vaccination;
use App\Models\citizens;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HealthRecordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX / LIST HEALTH RECORDS (WITH SEARCH, PAGINATION & HEATMAP)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
{
    if (!Auth::check() || Auth::user()->role !== 'bhw') {
        abort(403);
    }

    $search = $request->input('search');
    $purok = $request->input('purok');

    $query = citizens::with('healthRecords');

    // Filter by Purok if selected
    if ($purok && $purok !== 'all') {
        $query->where('Citizen_Purok', $purok);
    }

    // Filter by Search query (Citizen Name or Diagnosis)
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('Citizen_FName', 'LIKE', "%{$search}%")
              ->orWhere('Citizen_LName', 'LIKE', "%{$search}%")
              ->orWhereHas('healthRecords', function($subQuery) use ($search) {
                  $subQuery->where('diagnosis', 'LIKE', "%{$search}%");
              });
        });
    }

    $citizens = $query->paginate(10)->appends($request->all());

    // Define geographic coordinates for each Purok in Brgy. Amuyong
    $amuyongPurokCoordinates = [
        '1' => ['lat' => 14.0680, 'lng' => 120.8515],
        '2' => ['lat' => 14.0655, 'lng' => 120.8540],
        '3' => ['lat' => 14.0630, 'lng' => 120.8500],
        '4' => ['lat' => 14.0690, 'lng' => 120.8550],
        '5' => ['lat' => 14.0640, 'lng' => 120.8525],
    ];

    $allCitizens = citizens::with('healthRecords')->get();
    $heatmapData = [];

    foreach ($amuyongPurokCoordinates as $purokKey => $coords) {
        $matchingCitizens = $allCitizens->filter(function($c) use ($purokKey) {
            $dbPurok = preg_replace('/[^0-9]/', '', (string)$c->Citizen_Purok);
            return $dbPurok === (string)$purokKey;
        });

        // Count total health records/diagnoses in this specific Purok
        $recordCount = $matchingCitizens->sum(fn($c) => $c->healthRecords->count());

        // STRICT REQUIREMENT: Only include zones that have 1 or more diagnoses
        if ($recordCount > 0) {
            $heatmapData[] = [
                'location' => $coords,
                // Severity scaled threefold based on the number of diagnoses
                'weight' => $recordCount * 3,
                'purok' => $purokKey,
                'citizens_count' => $matchingCitizens->count(),
                'records_count' => $recordCount
            ];
        }
    }

    return view('bhw.healthrecord', compact('citizens', 'heatmapData'));
}

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
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        // Load citizen with health records and vaccinations
        $citizen = citizens::with([
            'healthRecords' => fn($q) => $q->latest(),
            'vaccinations' => fn($q) => $q->latest()
        ])->findOrFail($id);

        return view('bhw.citizen_show', compact('citizen'));
    }

    /*
    |--------------------------------------------------------------------------
    | STORE VACCINATION
    |--------------------------------------------------------------------------
    */
    public function storeVaccination(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'vaccine_name' => 'required|string|max:255',
            'date_administered' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Vaccination::create($request->all());

        return redirect()->back()->with('success', 'Vaccination added successfully.');
    }
}