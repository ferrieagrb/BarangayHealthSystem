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
use App\Models\Family;
use App\Models\Purok;

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

        // SEARCH CITIZENS (by first name, last name, ID, or diagnosis)
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('Citizen_FName', 'like', '%' . $search . '%')
                  ->orWhere('Citizen_LName', 'like', '%' . $search . '%')
                  ->orWhere('id', $search)
                  ->orWhereHas('healthRecords', function ($q2) use ($search) {
                      $q2->where('diagnosis', 'like', '%' . $search . '%');
                  });
            });
        }

        // FILTER BY PUROK
        if ($request->purok && $request->purok != 'all') {
            $purokValue = $request->purok;
            
            // Try to find if the value corresponds to a Purok ID
            $purokRecord = Purok::find($purokValue);
            if ($purokRecord) {
                // If it's an ID, filter by the purok name or ID depending on how your citizens table stores it
                $query->where(function($q) use ($purokRecord, $purokValue) {
                    $q->where('Citizen_Purok', $purokRecord->name)
                      ->orWhere('Citizen_Purok', $purokValue);
                });
            } else {
                // Otherwise treat it as a direct string match
                $query->where('Citizen_Purok', $purokValue);
            }
        }

        if ($request->subgroup && $request->subgroup != 'all') {
            $subgroupId = $request->subgroup;
            $query->whereHas('family', function ($q) use ($subgroupId) {
                $q->where('subgroup_id', $subgroupId);
            });
        }

        if ($request->filled('age_group')) {
            $ageGroup = $request->age_group;
            if ($ageGroup === 'kid') {
                $query->where('Citizen_Age', '<=', 17);
            } elseif ($ageGroup === 'adult') {
                $query->whereBetween('Citizen_Age', [18, 59]);
            } elseif ($ageGroup === 'senior') {
                $query->where('Citizen_Age', '>=', 60);
            }
        }

        $puroks = Purok::with('subgroups')->get();

        // PAGINATE AND PRESERVE SEARCH PARAMETERS
        $citizens = $query->paginate(10)->appends([
            'search' => $request->search,
            'purok' => $request->purok
        ]);

        $base = citizens::query();

        return view('bhw.citizen', [
            'citizens' => $citizens,
            'totalCitizens' => $base->count(),
            'kids' => (clone $base)->where('Citizen_Age', '<=', 17)->count(),
            'adults' => (clone $base)->whereBetween('Citizen_Age', [18, 59])->count(),
            'seniors' => (clone $base)->where('Citizen_Age', '>=', 60)->count(),
            'puroks' => $puroks,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE CITIZEN
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'Citizen_FName' => 'required|string|max:255',
            'Citizen_LName' => 'required|string|max:255',
            'Citizen_Age' => 'required|integer|min:0',
            'Citizen_BirthDate' => 'required|date',
            'Citizen_ContactNo' => 'nullable|string|max:50',
            'Citizen_Purok' => 'required|string|max:255',
        ]);

        $citizen = citizens::create($validated);

        $this->logActivity(
            'create',
            'citizen',
            $citizen->id,
            'Added new citizen: ' . $citizen->Citizen_FName . ' ' . $citizen->Citizen_LName
        );

        return redirect()->route('citizenlist')->with('success', 'Citizen added successfully.');
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
            'description' => 'Added health record: ' . $request->diagnosis,
        ]);

        return redirect()->back()->with('success', 'Health record added!');
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
            'description' => 'Viewed health record dashboard',
        ]);

        return view('bhw.healthrecord', compact('citizens', 'recentDiagnoses', 'totalRecords'));
    }

    /*
    |--------------------------------------------------------------------------
    | CITIZEN DETAIL PAGE
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $citizen = citizens::with('healthRecords')->findOrFail($id);

        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen health record'
        );

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'view',
            'citizen_id' => $citizen->id,
            'health_record_id' => null,
            'description' => 'Viewed health records of citizen ID ' . $citizen->id,
        ]);

        return view('bhw.citizen_show', compact('citizen'));
    }

    public function citizendetails($id)
    {
        $citizen = citizens::with('healthRecords','family.purok')->findOrFail($id);

        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen details page'
        );

        $families = Family::with('purok', 'subgroup')->get();

        return view('bhw.citizendetails', compact('citizen', 'families'));
    }

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

        return redirect()->route('citizenlist')
            ->with('success', 'Citizen deleted successfully.');
    }

    public function update(Request $request, $id)
    {
        $citizen = citizens::findOrFail($id);

        $citizen->update([
            'Citizen_FName' => $request->Citizen_FName,
            'Citizen_LName' => $request->Citizen_LName,
            'Citizen_Age' => $request->Citizen_Age,
            'Citizen_BirthDate' => $request->Citizen_BirthDate,
            'Citizen_ContactNo' => $request->Citizen_ContactNo,
            'Citizen_Purok' => $request->Citizen_Purok,
            'family_id' => $request->family_id,
        ]);

        $this->logActivity(
            'update',
            'citizen',
            $citizen->id,
            'Updated citizen details'
        );

        return redirect()->back()->with('success', 'Citizen updated successfully.');
    }

    private function logActivity($action, $module, $citizenId = null, $description = null)
    {
        CitizenActivityLog::create([
            'user_id' => Auth::id() ?? 0,
            'action' => $action,
            'module' => $module,
            'citizen_id' => $citizenId,
            'description' => $description,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        if (($handle = fopen($path, 'r')) !== FALSE) {
            $header = fgetcsv($handle, 1000, ',');

            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                citizens::create([
                    'Citizen_FName'     => $row[0] ?? '',
                    'Citizen_LName'     => $row[1] ?? '',
                    'Citizen_Age'       => $row[2] ?? 0,
                    'Citizen_BirthDate' => $row[3] ?? null,
                    'Citizen_ContactNo' => $row[4] ?? '',
                    'Citizen_Purok'     => $row[5] ?? 'Purok 1',
                ]);
            }
            fclose($handle);
        }

        $this->logActivity('import', 'citizen', null, 'Imported citizens via CSV/Excel spreadsheet');

        return redirect()->route('citizenlist')->with('success', 'Citizens imported successfully!');
    }

    public function showElectronicCard($id)
    {
        $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])->findOrFail($id);
        return view('bhw.ecard', compact('citizen'));
    }

    public function storeVaccination(Request $request, $id)
    {
        $validated = $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'dose_number' => 'required|string|max:100',
            'date_administered' => 'required|date',
            'administered_by' => 'required|string|max:255',
        ]);
        $validated['citizen_id'] = $id;
        VaccinationRecord::create($validated);

        $this->logActivity(
            'create',
            'vaccination',
            $id,
            'Administered vaccine (' . $validated['vaccine_name'] . ' - ' . $validated['dose_number'] . ') to citizen ID ' . $id
        );

        return back()->with('success', 'Vaccination record added successfully.');
    }

    public function storeMedication(Request $request, $id)
    {
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'quantity_dispensed' => 'required|integer|min:1',
            'date_dispensed' => 'required|date',
        ]);
        $validated['citizen_id'] = $id;
        MedicationRecord::create($validated);

        $this->logActivity(
            'create',
            'medication',
            $id,
            'Dispensed medication (' . $validated['medicine_name'] . ' x' . $validated['quantity_dispensed'] . ') to citizen ID ' . $id
        );

        return back()->with('success', 'Medication log added successfully.');
    }

    public function citizenViewECard()
    {
        $user = Auth::user();
        $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])
                    ->where('id', $user->citizen_id)
                    ->firstOrFail();

        return view('citizen.ecard', compact('citizen'));
    }
}