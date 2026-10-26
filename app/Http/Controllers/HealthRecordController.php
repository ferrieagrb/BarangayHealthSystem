<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HealthRecord;
use App\Models\Vaccination;
use App\Models\citizens;
use App\Models\MaintenanceRecord;
use App\Models\Supply;
use App\Models\Purok;
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

    $search = trim((string) $request->input('search', ''));
    $purok = $request->input('purok');
    $puroks = Purok::orderBy('name')->get();

    $query = citizens::with('healthRecords');

    // Filter by Purok if selected
    if ($purok && $purok !== 'all') {
        $purokName = $puroks->firstWhere('id', $purok)?->name ?? $purok;
        $query->where(function ($builder) use ($purokName, $purok) {
            $builder->where('Citizen_Purok', $purokName)
                ->orWhere('Citizen_Purok', $purok);
        });
    }

    // Filter by Search query (Citizen Name or Diagnosis)
    if ($search) {
        $searchTerms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [$search];
        $query->where(function ($builder) use ($search, $searchTerms) {
            $builder->where(function ($nameQuery) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $nameQuery->where(function ($termQuery) use ($term) {
                        $termQuery->where('Citizen_FName', 'like', "%{$term}%")
                            ->orWhere('Citizen_MName', 'like', "%{$term}%")
                            ->orWhere('Citizen_LName', 'like', "%{$term}%");
                    });
                }
            })->orWhereHas('healthRecords', function ($subQuery) use ($search) {
                $subQuery->where('diagnosis', 'like', "%{$search}%");
            });
        });
    }

    if ($request->filled('health_filter_sex') && $request->input('health_filter_sex') !== 'all') {
        $query->where('Citizen_Sex', ucfirst($request->input('health_filter_sex')));
    }

    $housingFilters = (array) $request->input('health_filter_housing', []);
    if ($housingFilters) {
        $housingValues = array_map(
            fn ($value) => $value === 'permanent_resident' ? 'Permanent Resident' : 'Renter',
            array_intersect($housingFilters, ['permanent_resident', 'renter'])
        );
        if ($housingValues) {
            $query->whereIn('Citizen_HousingStatus', $housingValues);
        }
    }

    $healthFilters = (array) $request->input('health_filter_health', []);
    if ($healthFilters) {
        $query->where(function ($builder) use ($healthFilters) {
            if (in_array('pregnant', $healthFilters, true)) {
                $builder->orWhere('Citizen_Pregnant', true);
            }
            if (in_array('in_pills', $healthFilters, true)) {
                $builder->orWhere('Citizen_InPills', true);
            }
        });
    }

    $vaccineFilters = (array) $request->input('health_filter_vaccine', []);
    if ($vaccineFilters) {
        $vaccineTerms = [
            'bcg' => ['bcg'], 'hepb' => ['hepatitis b', 'hepb'], 'penta' => ['pentavalent', 'penta'],
            'opv' => ['oral polio', 'opv'], 'ipv' => ['inactivated polio', 'ipv'], 'pcv' => ['pneumococcal', 'pcv'],
            'mmr' => ['measles, mumps', 'mmr'], 'mr' => ['measles-rubella', 'mr'], 'td' => ['tetanus', 'td'],
            'hpv' => ['human papillomavirus', 'hpv'],
        ];
        $query->whereHas('vaccinations', function ($vaccinationQuery) use ($vaccineFilters, $vaccineTerms) {
            $vaccinationQuery->where(function ($builder) use ($vaccineFilters, $vaccineTerms) {
                foreach ($vaccineFilters as $filter) {
                    foreach ($vaccineTerms[$filter] ?? [] as $term) {
                        $builder->orWhere('vaccine_name', 'like', "%{$term}%");
                    }
                }
            });
        });
    }

    if ($request->boolean('filter_deceased')) {
        $query->where('Citizen_IsDeceased', true);
    } else {
        $query->where(function ($builder) {
            $builder->whereNull('Citizen_IsDeceased')
                ->orWhere('Citizen_IsDeceased', false);
        });
    }

    $citizens = $query->paginate(10)->withQueryString();

    $allCitizens = citizens::with('healthRecords')->get();
    $heatmapData = [];

    foreach ($puroks->whereNotNull('latitude')->whereNotNull('longitude') as $purok) {
        $purokKey = preg_replace('/[^0-9]/', '', (string) $purok->name);
        $matchingCitizens = $allCitizens->filter(function($c) use ($purokKey) {
            $dbPurok = preg_replace('/[^0-9]/', '', (string)$c->Citizen_Purok);
            return $dbPurok === (string)$purokKey;
        });

        // Count total health records/diagnoses in this specific Purok
        $recordCount = $matchingCitizens->sum(fn($c) => $c->healthRecords->count());

        // STRICT REQUIREMENT: Only include zones that have 1 or more diagnoses
        if ($recordCount > 0) {
            $heatmapData[] = [
                'location' => ['lat' => (float) $purok->latitude, 'lng' => (float) $purok->longitude],
                // Severity scaled threefold based on the number of diagnoses
                'weight' => $recordCount * 3,
                'purok' => $purok->name,
                'citizens_count' => $matchingCitizens->count(),
                'records_count' => $recordCount
            ];
        }
    }

    return view('bhw.healthrecord', compact('citizens', 'heatmapData', 'puroks'));
}

    /*
    |--------------------------------------------------------------------------
    | STORE HEALTH RECORD
    |--------------------------------------------------------------------------
    */
    public function store(Request $request, $id = null)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $validated = $request->validate([
            'citizen_id' => $id ? 'nullable|integer' : 'required|exists:citizens,id',
            'diagnosis' => 'required|string|max:2000',
            'record_date' => 'required|date',
            'comments' => 'nullable|string|max:2000',
            'severity' => 'nullable|in:Low / Mild,Moderate,High / Severe,Critical,Resolved',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:1|max:300',
            'temperature' => 'nullable|numeric|min:25|max:45',
            'blood_pressure' => 'nullable|string|max:20',
        ]);

        $citizenId = $id ?: $request->input('citizen_id');
        $citizen = \App\Models\citizens::findOrFail($citizenId);

        // Prevent a profile URL from being used to post a record for another citizen.
        if ($request->filled('citizen_id') && (int) $request->input('citizen_id') !== (int) $citizen->id) {
            abort(422, 'Citizen ID mismatch.');
        }

        $weight = $validated['weight'] ?? null;
        $height = $validated['height'] ?? null;
        $bmi = ($weight && $height) ? round($weight / (($height / 100) ** 2), 1) : null;

        HealthRecord::create([
            'citizen_id' => $citizen->id,
            'diagnosis' => trim($validated['diagnosis']),
            'record_date' => $validated['record_date'],
            'comments' => isset($validated['comments']) ? trim($validated['comments']) : null,
            'severity' => $validated['severity'] ?? 'Low / Mild',
            'weight' => $weight,
            'height' => $height,
            'bmi' => $bmi,
            'temperature' => $validated['temperature'] ?? null,
            'blood_pressure' => isset($validated['blood_pressure']) ? trim($validated['blood_pressure']) : null,
        ]);

        // Keep the citizen's latest weight/height in sync with the profile where these fields exist.
        $profileUpdates = [];
        if ($weight !== null) $profileUpdates['Citizen_Weight'] = $weight;
        if ($height !== null) $profileUpdates['Citizen_Height'] = $height;
        if ($profileUpdates) $citizen->forceFill($profileUpdates)->save();

        return redirect()->back()->with('success', 'Diagnosis added successfully.');
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
            'vaccinations' => fn($q) => $q->latest(),
            'maintenanceRecords' => fn($q) => $q->latest()
        ])->findOrFail($id);

        $availableMedicines = Supply::where('quantity', '>', 0)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->paginate(10, ['*'], 'medicine_page');

        return view('bhw.citizen_show', compact('citizen', 'availableMedicines'));
    }

    public function storeMaintenance(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $validated = $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'medicine_name' => 'required|string|max:255',
            'quantity_dispensed' => 'required|integer|min:1|max:100000',
        ]);

        MaintenanceRecord::create([
            'citizen_id' => $validated['citizen_id'],
            'medicine_name' => trim($validated['medicine_name']),
            'quantity_dispensed' => $validated['quantity_dispensed'],
            // Keep legacy columns populated for compatibility with existing records/schema.
            'dosage' => 'N/A',
            'frequency' => 'As prescribed',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'notes' => null,
        ]);

        return redirect()->back()->with('success', 'Maintenance medicine added successfully.');
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