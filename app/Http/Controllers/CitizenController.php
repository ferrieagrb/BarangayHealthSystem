<?php

namespace App\Http\Controllers;

use App\Models\CitizenActivityLog;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\HealthRecordActivityLog;
use App\Models\VaccinationRecord;
use App\Models\MedicationRecord;
use App\Models\Family;
use App\Models\Purok;
use App\Imports\CitizensImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class CitizenController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CITIZEN LIST PAGE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        Auth::id();

        /*
        |--------------------------------------------------------------------------
        | FIX EXISTING AGE VALUES
        |--------------------------------------------------------------------------
        | Birth date is the source of truth.
        | This corrects old records where Citizen_Age does not match
        | Citizen_BirthDate.
        |--------------------------------------------------------------------------
        */
        citizens::whereNotNull('Citizen_BirthDate')
            ->chunkById(100, function ($citizens) {

                foreach ($citizens as $citizen) {

                    try {

                        $birthDate = Carbon::parse(
                            $citizen->Citizen_BirthDate
                        );

                        if ($birthDate->isFuture()) {
                            continue;
                        }

                        $correctAge = $birthDate->age;

                        if ((int) $citizen->Citizen_Age !== $correctAge) {

                            $citizen->update([
                                'Citizen_Age' => $correctAge
                            ]);
                        }

                    } catch (\Exception $e) {
                        // Ignore invalid birth dates
                    }
                }
            });


        $query = citizens::query();


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = trim($request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'Citizen_FName',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhere(
                    'Citizen_LName',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhere(
                    'id',
                    $search
                )

                ->orWhereHas(
                    'healthRecords',
                    function ($q2) use ($search) {

                        $q2->where(
                            'diagnosis',
                            'like',
                            '%' . $search . '%'
                        );
                    }
                );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BY PUROK
        |--------------------------------------------------------------------------
        */
        if (
            $request->filled('purok') &&
            $request->purok !== 'all'
        ) {

            $purokValue = $request->purok;

            $purokRecord = Purok::find($purokValue);

            if ($purokRecord) {

                $query->where(function ($q) use (
                    $purokRecord,
                    $purokValue
                ) {

                    $q->where(
                        'Citizen_Purok',
                        $purokRecord->name
                    )

                    ->orWhere(
                        'Citizen_Purok',
                        $purokValue
                    );
                });

            } else {

                $query->where(
                    'Citizen_Purok',
                    $purokValue
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BY SUBGROUP
        |--------------------------------------------------------------------------
        */
        if (
            $request->filled('subgroup') &&
            $request->subgroup !== 'all'
        ) {

            $subgroupId = $request->subgroup;

            $query->whereHas(
                'family',
                function ($q) use ($subgroupId) {

                    $q->where(
                        'subgroup_id',
                        $subgroupId
                    );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BY AGE RANGE
        |--------------------------------------------------------------------------
        | Examples:
        | 8
        | 4-10
        | 10-4
        |--------------------------------------------------------------------------
        */
        if ($request->filled('age')) {

            $age = trim($request->age);

            if (
                preg_match(
                    '/^(\d+)\s*-\s*(\d+)$/',
                    $age,
                    $matches
                )
            ) {

                $minAge = (int) $matches[1];
                $maxAge = (int) $matches[2];

                if ($minAge > $maxAge) {

                    [$minAge, $maxAge] =
                        [$maxAge, $minAge];
                }

                $query->whereBetween(
                    'Citizen_Age',
                    [$minAge, $maxAge]
                );

            } elseif (is_numeric($age)) {

                $query->where(
                    'Citizen_Age',
                    (int) $age
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | FILTER BY AGE GROUP
        |--------------------------------------------------------------------------
        */
        if ($request->filled('age_group')) {

            $ageGroup = $request->age_group;

            if ($ageGroup === 'kid') {

                $query->where(
                    'Citizen_Age',
                    '<=',
                    17
                );

            } elseif ($ageGroup === 'adult') {

                $query->whereBetween(
                    'Citizen_Age',
                    [18, 59]
                );

            } elseif ($ageGroup === 'senior') {

                $query->where(
                    'Citizen_Age',
                    '>=',
                    60
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PUROKS + SUBGROUPS
        |--------------------------------------------------------------------------
        */
        $puroks = Purok::with('subgroups')->get();


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        $citizens = $query
            ->paginate(10)
            ->appends([
                'search' => $request->search,
                'purok' => $request->purok,
                'subgroup' => $request->subgroup,
                'age' => $request->age,
                'age_group' => $request->age_group,
            ]);


        /*
        |--------------------------------------------------------------------------
        | DASHBOARD COUNTS
        |--------------------------------------------------------------------------
        */
        $base = citizens::query();

        $totalCitizens =
            $base->count();

        $kids =
            (clone $base)
                ->where(
                    'Citizen_Age',
                    '<=',
                    17
                )
                ->count();

        $adults =
            (clone $base)
                ->whereBetween(
                    'Citizen_Age',
                    [18, 59]
                )
                ->count();

        $seniors =
            (clone $base)
                ->where(
                    'Citizen_Age',
                    '>=',
                    60
                )
                ->count();


        return view(
            'bhw.citizen',
            compact(
                'citizens',
                'totalCitizens',
                'kids',
                'adults',
                'seniors',
                'puroks'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE CITIZEN & USER ACCOUNT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Citizen_FName' => 'required|string|max:255',
            'Citizen_LName' => 'required|string|max:255',
            'Citizen_BirthDate' => 'required|date|before_or_equal:today',
            'Citizen_ContactNo' => 'required|string|max:50',
            'Citizen_Purok' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        // Calculate age
        $validated['Citizen_Age'] = Carbon::parse($validated['Citizen_BirthDate'])->age;

        // 1. Create the citizen profile record first
        $citizen = citizens::create([
            'Citizen_FName' => $validated['Citizen_FName'],
            'Citizen_LName' => $validated['Citizen_LName'],
            'Citizen_BirthDate' => $validated['Citizen_BirthDate'],
            'Citizen_Age' => $validated['Citizen_Age'],
            'Citizen_ContactNo' => $validated['Citizen_ContactNo'],
            'Citizen_Purok' => $validated['Citizen_Purok'],
        ]);

        // 2. Create the corresponding login account in the users table with role 'citizen'
        \App\Models\User::create([
            'name' => $validated['Citizen_FName'] . ' ' . $validated['Citizen_LName'],
            'email' => $validated['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            'role' => 'citizen',         // Automatically sets the role to citizen
            'citizen_id' => $citizen->id // Links the user account back to the citizen profile
        ]);

        $this->logActivity(
            'create',
            'citizen',
            $citizen->id,
            'Added new citizen and account: ' . $citizen->Citizen_FName . ' ' . $citizen->Citizen_LName
        );

        return redirect()
            ->route('citizenlist')
            ->with('success', 'Citizen and portal account created successfully.');
    }


    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORD STORE
    |--------------------------------------------------------------------------
    */
    public function storeHealthRecord(Request $request)
    {
        $request->validate([

            'citizen_id' =>
                'required|exists:citizens,id',

            'diagnosis' =>
                'required|string',

            'record_date' =>
                'required|date',

            'comments' =>
                'nullable|string',
        ]);


        $record = HealthRecord::create([

            'citizen_id' =>
                $request->citizen_id,

            'diagnosis' =>
                $request->diagnosis,

            'record_date' =>
                $request->record_date,

            'comments' =>
                $request->comments,
        ]);


        HealthRecordActivityLog::create([

            'user_id' =>
                Auth::id(),

            'action' =>
                'create',

            'citizen_id' =>
                $request->citizen_id,

            'health_record_id' =>
                $record->id,

            'description' =>
                'Added health record: ' .
                $request->diagnosis,
        ]);


        return redirect()
            ->back()
            ->with(
                'success',
                'Health record added!'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORD PAGE
    |--------------------------------------------------------------------------
    */
    public function healthIndex()
    {
        $citizens =
            citizens::paginate(10);


        $recentDiagnoses =
            HealthRecord::with('citizen')
                ->latest()
                ->take(5)
                ->get();


        $totalRecords =
            HealthRecord::count();


        HealthRecordActivityLog::create([

            'user_id' =>
                Auth::id(),

            'action' =>
                'view',

            'citizen_id' =>
                null,

            'health_record_id' =>
                null,

            'description' =>
                'Viewed health record dashboard',
        ]);


        return view(
            'bhw.healthrecord',
            compact(
                'citizens',
                'recentDiagnoses',
                'totalRecords'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CITIZEN DETAIL PAGE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $citizen =
            citizens::with([
                'healthRecords'
            ])->findOrFail($id);


        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen health record'
        );


        HealthRecordActivityLog::create([

            'user_id' =>
                Auth::id(),

            'action' =>
                'view',

            'citizen_id' =>
                $citizen->id,

            'health_record_id' =>
                null,

            'description' =>
                'Viewed health records of citizen ID ' .
                $citizen->id,
        ]);


        return view(
            'bhw.citizen_show',
            compact('citizen')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CITIZEN DETAILS PAGE
    |--------------------------------------------------------------------------
    */
    public function citizendetails($id)
    {
        $citizen =
            citizens::with([
                'healthRecords',
                'family.purok'
            ])->findOrFail($id);


        $families =
            Family::with([
                'purok',
                'subgroup'
            ])->get();


        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen details page'
        );


        return view(
            'bhw.citizendetails',
            compact(
                'citizen',
                'families'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE CITIZEN
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $citizen =
            citizens::findOrFail($id);


        $this->logActivity(
            'delete',
            'citizen',
            $citizen->id,
            'Deleted citizen'
        );


        $citizen->delete();


        return redirect()
            ->route('citizenlist')
            ->with(
                'success',
                'Citizen deleted successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE CITIZEN
    |--------------------------------------------------------------------------
    */
    public function update(
        Request $request,
        $id
    ) {

        $citizen =
            citizens::findOrFail($id);

        $validated = $request->validate([

            'Citizen_FName' =>
                'required|string|max:255',

            'Citizen_LName' =>
                'required|string|max:255',

            'Citizen_BirthDate' =>
                'required|date|before_or_equal:today',

            'Citizen_ContactNo' =>
                'nullable|string|max:50',

            'Citizen_Purok' =>
                'required|string|max:255',

            'family_id' =>
                'nullable|exists:families,id',
        ]);


        /*
        |--------------------------------------------------------------------------
        | ALWAYS RECALCULATE AGE
        |--------------------------------------------------------------------------
        */
        $validated['Citizen_Age'] =
            Carbon::parse(
                $validated['Citizen_BirthDate']
            )->age;


        $citizen->update(
            $validated
        );


        $this->logActivity(
            'update',
            'citizen',
            $citizen->id,
            'Updated citizen details'
        );


        return redirect()
            ->back()
            ->with(
                'success',
                'Citizen updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
    private function logActivity(
        $action,
        $module,
        $citizenId = null,
        $description = null
    ) {

        CitizenActivityLog::create([

            'user_id' =>
                Auth::id() ?? 0,

            'action' =>
                $action,

            'module' =>
                $module,

            'citizen_id' =>
                $citizenId,

            'description' =>
                $description,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT CITIZENS
    |--------------------------------------------------------------------------
    | Supports:
    | CSV
    | XLS
    | XLSX
    |--------------------------------------------------------------------------
    */
    public function import(Request $request)
    {
        $request->validate([

            'file' =>
                'required|mimes:csv,txt,xlsx,xls|max:2048',
        ]);


        try {

            $file =
                $request->file('file');


            Excel::import(
                new CitizensImport,
                $file
            );


            $this->logActivity(
                'import',
                'citizen',
                null,
                'Imported citizens via CSV/Excel spreadsheet'
            );


            return redirect()
                ->route('citizenlist')
                ->with(
                    'success',
                    'Citizens imported successfully!'
                );

        } catch (\Exception $e) {

            return redirect()
                ->back()
                ->with(
                    'error',
                    'Import failed: ' .
                    $e->getMessage()
                );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BHW ELECTRONIC CARD
    |--------------------------------------------------------------------------
    */
    public function showElectronicCard($id)
    {
        $citizen =
            citizens::with([
                'vaccinations',
                'medications',
                'healthRecords'
            ])->findOrFail($id);


        return view(
            'bhw.ecard',
            compact('citizen')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE VACCINATION RECORD
    |--------------------------------------------------------------------------
    */
    public function storeVaccination(
        Request $request,
        $id
    ) {

        $validated =
            $request->validate([

                'vaccine_name' =>
                    'required|string|max:255',

                'dose_number' =>
                    'required|string|max:100',

                'date_administered' =>
                    'required|date',

                'administered_by' =>
                    'required|string|max:255',
            ]);


        $validated['citizen_id'] =
            $id;


        VaccinationRecord::create(
            $validated
        );


        $this->logActivity(
            'create',
            'vaccination',
            $id,
            'Administered vaccine (' .
            $validated['vaccine_name'] .
            ' - ' .
            $validated['dose_number'] .
            ') to citizen ID ' .
            $id
        );


        return back()->with(
            'success',
            'Vaccination record added successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE MEDICATION RECORD
    |--------------------------------------------------------------------------
    */
    public function storeMedication(
        Request $request,
        $id
    ) {

        $validated =
            $request->validate([

                'medicine_name' =>
                    'required|string|max:255',

                'dosage' =>
                    'required|string|max:100',

                'quantity_dispensed' =>
                    'required|integer|min:1',

                'date_dispensed' =>
                    'required|date',
            ]);


        $validated['citizen_id'] =
            $id;


        MedicationRecord::create(
            $validated
        );


        $this->logActivity(
            'create',
            'medication',
            $id,
            'Dispensed medication (' .
            $validated['medicine_name'] .
            ' x' .
            $validated['quantity_dispensed'] .
            ') to citizen ID ' .
            $id
        );


        return back()->with(
            'success',
            'Medication log added successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CITIZEN SELF-SERVICE E-CARD
    |--------------------------------------------------------------------------
    */
    public function citizenViewECard()
    {
        $user =
            Auth::user();


        $citizen =
            citizens::with([
                'vaccinations',
                'medications',
                'healthRecords'
            ])
            ->where(
                'id',
                $user->citizen_id
            )
            ->firstOrFail();


        return view(
            'citizen.ecard',
            compact('citizen')
        );
    }
}