<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\CitizenActivityLog;
use App\Models\HealthRecordActivityLog;
use Illuminate\Support\Facades\Auth;
use App\Models\VaccinationRecord;
use App\Models\MedicationRecord;


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

        $query = citizens::query();

        // SEARCH CITIZENS
        // by first name, last name, ID, or diagnosis
        if ($request->search) {

            $search = $request->search;

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


        // FILTER BY PUROK
        if ($request->purok && $request->purok != 'all') {

            $query->where(
                'Citizen_Purok',
                $request->purok
            );
        }


        // FILTER BY AGE
        //
        // Examples:
        // 8     = exactly 8 years old
        // 4-10  = ages 4 through 10
        // 10-4  = automatically becomes 4 through 10
        //
        if ($request->filled('age')) {

            $age = trim($request->age);


            // AGE RANGE
            // Example: 4-10
            if (
                preg_match(
                    '/^(\d+)\s*-\s*(\d+)$/',
                    $age,
                    $matches
                )
            ) {

                $minAge = (int) $matches[1];
                $maxAge = (int) $matches[2];


                // If user enters 10-4,
                // automatically reverse it to 4-10
                if ($minAge > $maxAge) {

                    [$minAge, $maxAge] =
                        [$maxAge, $minAge];
                }


                $query->whereBetween(
                    'Citizen_Age',
                    [$minAge, $maxAge]
                );
            }


            // SINGLE AGE
            // Example: 8
            elseif (is_numeric($age)) {

                $query->where(
                    'Citizen_Age',
                    (int) $age
                );
            }
        }


        // PAGINATE AND PRESERVE ALL FILTERS
        $citizens = $query
            ->paginate(10)
            ->appends([
                'search' => $request->search,
                'purok' => $request->purok,
                'age' => $request->age
            ]);


        // DASHBOARD COUNTS
        $base = citizens::query();

        return view('bhw.citizen', [

            'citizens' => $citizens,

            'totalCitizens' =>
                $base->count(),

            'kids' =>
                (clone $base)
                    ->where(
                        'Citizen_Age',
                        '<=',
                        17
                    )
                    ->count(),

            'adults' =>
                (clone $base)
                    ->whereBetween(
                        'Citizen_Age',
                        [18, 59]
                    )
                    ->count(),

            'seniors' =>
                (clone $base)
                    ->where(
                        'Citizen_Age',
                        '>=',
                        60
                    )
                    ->count(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE CITIZEN
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $citizen = citizens::create($request->all());

        $this->logActivity(
            'create',
            'citizen',
            $citizen->id,
            'Added new citizen'
        );

        return redirect()->route('citizenlist');
    }


    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORD STORE
    |--------------------------------------------------------------------------
    */
    public function storeHealthRecord(Request $request)
    {
        $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'diagnosis' => 'required|string',
            'record_date' => 'required|date',
            'comments' => 'nullable|string',
        ]);

        $record = HealthRecord::create([
            'citizen_id' => $request->citizen_id,
            'diagnosis' => $request->diagnosis,
            'record_date' => $request->record_date,
            'comments' => $request->comments,
        ]);

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'citizen_id' => $request->citizen_id,
            'health_record_id' => $record->id,
            'description' =>
                'Added health record: ' .
                $request->diagnosis,
        ]);

        return redirect()
            ->back()
            ->with('success', 'Health record added!');
    }


    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORD PAGE
    |--------------------------------------------------------------------------
    */
    public function healthIndex()
    {
        $citizens = citizens::paginate(10);

        $recentDiagnoses = HealthRecord::with('citizen')
            ->latest()
            ->take(5)
            ->get();

        $totalRecords = HealthRecord::count();

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'view',
            'citizen_id' => null,
            'health_record_id' => null,
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
        $citizen = citizens::with('healthRecords')
            ->findOrFail($id);

        // Citizen activity log
        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen health record'
        );

        // Health record activity log
        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'view',
            'citizen_id' => $citizen->id,
            'health_record_id' => null,
            'description' =>
                'Viewed health records of citizen ID ' .
                $citizen->id,
        ]);

        return view(
            'bhw.citizen_show',
            compact('citizen')
        );
    }


    public function citizendetails($id)
    {
        $citizen = citizens::with('healthRecords')
            ->findOrFail($id);

        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen details page'
        );

        return view(
            'bhw.citizendetails',
            compact('citizen')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE CITIZEN
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $citizen = citizens::findOrFail($id);

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
    public function update(Request $request, $id)
    {
        $citizen = citizens::findOrFail($id);

        $citizen->update([
            'Citizen_FName' =>
                $request->Citizen_FName,

            'Citizen_LName' =>
                $request->Citizen_LName,

            'Citizen_Age' =>
                $request->Citizen_Age,

            'Citizen_BirthDate' =>
                $request->Citizen_BirthDate,

            'Citizen_ContactNo' =>
                $request->Citizen_ContactNo,

            'Citizen_Purok' =>
                $request->Citizen_Purok,
        ]);

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
    */
    public function import(Request $request)
    {
        $request->validate([
            'file' =>
                'required|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        // Read CSV file rows
        if (($handle = fopen($path, 'r')) !== FALSE) {

            // Skip header row
            $header = fgetcsv(
                $handle,
                1000,
                ','
            );

            while (
                ($row = fgetcsv(
                    $handle,
                    1000,
                    ','
                )) !== FALSE
            ) {

                // CSV columns:
                // First Name,
                // Last Name,
                // Age,
                // BirthDate,
                // ContactNo,
                // Purok

                citizens::create([

                    'Citizen_FName' =>
                        $row[0] ?? '',

                    'Citizen_LName' =>
                        $row[1] ?? '',

                    'Citizen_Age' =>
                        $row[2] ?? 0,

                    'Citizen_BirthDate' =>
                        $row[3] ?? null,

                    'Citizen_ContactNo' =>
                        $row[4] ?? '',

                    'Citizen_Purok' =>
                        $row[5] ?? 'Purok 1',
                ]);
            }

            fclose($handle);
        }

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
    }


    /*
    |--------------------------------------------------------------------------
    | BHW ELECTRONIC CARD
    |--------------------------------------------------------------------------
    */
    public function showElectronicCard($id)
    {
        $citizen = citizens::with([
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

        $validated = $request->validate([

            'vaccine_name' =>
                'required|string|max:255',

            'dose_number' =>
                'required|string|max:100',

            'date_administered' =>
                'required|date',

            'administered_by' =>
                'required|string|max:255',
        ]);

        $validated['citizen_id'] = $id;

        VaccinationRecord::create(
            $validated
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

        $validated = $request->validate([

            'medicine_name' =>
                'required|string|max:255',

            'dosage' =>
                'required|string|max:100',

            'quantity_dispensed' =>
                'required|integer|min:1',

            'date_dispensed' =>
                'required|date',
        ]);

        $validated['citizen_id'] = $id;

        MedicationRecord::create(
            $validated
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
        $user = Auth::user();

        $citizen = citizens::with([
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