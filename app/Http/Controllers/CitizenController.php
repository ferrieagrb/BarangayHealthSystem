<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Color\Color;
use App\Models\CitizenActivityLog;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\HealthRecordActivityLog;
use App\Models\VaccinationRecord;
use App\Models\MaintenanceRecord;
use App\Models\MedicationRecord;
use App\Models\Supply;
use App\Models\Family;
use App\Models\Purok;
use App\Exports\CitizensExport;
use App\Imports\CitizensImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\PurokSubgroup;
use App\Models\UserPermission;
use App\Traits\LogsActivity;

class CitizenController extends Controller
{
    use LogsActivity;
    /**
     * Helper method to authorize BHW edit/delete actions based on assigned subgroups.
     */
    private function authorizeCitizenEdit(citizens $citizen)
    {
        if ($this->canEditCitizen($citizen)) {
            return true;
        }

        abort(403, 'Unauthorized action. You can only edit citizens assigned to your purok subgroup.');
    }

    private function canEditCitizen(citizens $citizen): bool
    {
        $user = auth()->user();
        $citizen->loadMissing('family');

            // Check if citizen belongs to one of the BHW's assigned purok subgroups
            $assignedSubgroupIDs = PurokSubgroup::where('user_id', $user->id)->pluck('id');

            $family = $citizen->getAttribute('family');
            $belongsToAssignedSubgroup = $family
                && $assignedSubgroupIDs->contains($family->getAttribute('subgroup_id'));

            if ($belongsToAssignedSubgroup) {
                return true;
            }
        if (!$user || $user->role !== 'bhw') {
            return true;
        }

        $assignedSubgroupIDs = PurokSubgroup::where('user_id', $user->id)->pluck('id');

        return $assignedSubgroupIDs->contains($citizen->subgroup_id)
            || ($citizen->family && $assignedSubgroupIDs->contains($citizen->family->subgroup_id));
    }

    private function restrictToAssignedCitizens($query)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'bhw') {
            return $query;
        }

        $canViewAll = UserPermission::where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();

        if ($canViewAll) {
            return $query;
        }

        $assignedSubgroupIDs = PurokSubgroup::where('user_id', $user->id)->pluck('id');

        return $query->where(function ($citizenQuery) use ($assignedSubgroupIDs) {
            $citizenQuery->whereIn('subgroup_id', $assignedSubgroupIDs)
                ->orWhereHas('family', function ($familyQuery) use ($assignedSubgroupIDs) {
                    $familyQuery->whereIn('subgroup_id', $assignedSubgroupIDs);
                });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CITIZEN LIST PAGE (All BHWs can view all citizens)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FIX EXISTING AGE VALUES
        |--------------------------------------------------------------------------
        */
        citizens::whereNotNull('Citizen_BirthDate')
            ->chunkById(100, function ($citizens) {
                foreach ($citizens as $citizen) {
                    try {
                        $birthDate = Carbon::parse($citizen->Citizen_BirthDate);

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
        $summaryQuery = clone $query;

        /*
        |--------------------------------------------------------------------------
        | STATUS / MODAL FILTERS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('filter_sex') && $request->filter_sex !== 'all') {
            $query->where('Citizen_Sex', $request->filter_sex);
        }

        if ($request->filled('filter_housing')) {
            $housing = $request->filter_housing;
            $housingValues = is_array($housing) ? $housing : [$housing];
            $query->whereIn('Citizen_HousingStatus', $housingValues);
        }

        if ($request->filled('filter_health')) {
            $health = $request->filter_health;
            $healthValues = is_array($health) ? $health : [$health];

            if (in_array('pregnant', $healthValues)) {
                $query->where('Citizen_Pregnant', 1);
            }
            if (in_array('in_pills', $healthValues)) {
                $query->where('Citizen_InPills', 1);
            }
        }

        if (!$request->boolean('filter_deceased')) {
            $query->where(function ($q) {
                $q->whereNull('Citizen_IsDeceased')
                  ->orWhere('Citizen_IsDeceased', 0);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('Citizen_FName', 'like', '%' . $search . '%')
                  ->orWhere('Citizen_LName', 'like', '%' . $search . '%')
                  ->orWhere('id', $search)
                  ->orWhereHas('healthRecords', function ($q2) use ($search) {
                      $q2->where('diagnosis', 'like', '%' . $search . '%');
                  });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY PUROK
        |--------------------------------------------------------------------------
        */
        if ($request->filled('purok') && $request->purok !== 'all') {
            $purokValue = $request->purok;
            $purokRecord = Purok::find($purokValue);

            if ($purokRecord) {
                $query->where(function ($q) use ($purokRecord, $purokValue) {
                    $q->where('Citizen_Purok', $purokRecord->name)
                      ->orWhere('Citizen_Purok', $purokValue);
                });
            } else {
                $query->where('Citizen_Purok', $purokValue);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY SUBGROUP
        |--------------------------------------------------------------------------
        */
        if ($request->filled('subgroup') && $request->subgroup !== 'all') {
            $subgroupId = $request->subgroup;

            $query->where(function ($q) use ($subgroupId) {
                $q->where('subgroup_id', $subgroupId)
                    ->orWhereHas('family', function ($familyQuery) use ($subgroupId) {
                        $familyQuery->where('subgroup_id', $subgroupId);
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER BY AGE RANGE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('age')) {
            $age = trim($request->age);

            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $age, $matches)) {
                $minAge = (int) $matches[1];
                $maxAge = (int) $matches[2];

                if ($minAge > $maxAge) {
                    [$minAge, $maxAge] = [$maxAge, $minAge];
                }

                $query->whereBetween('Citizen_Age', [$minAge, $maxAge]);
            } elseif (is_numeric($age)) {
                $query->where('Citizen_Age', (int) $age);
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
                $query->where('Citizen_Age', '<=', 17);
            } elseif ($ageGroup === 'adult') {
                $query->whereBetween('Citizen_Age', [18, 59]);
            } elseif ($ageGroup === 'senior') {
                $query->where('Citizen_Age', '>=', 60);
            }
        }

        $puroks = Purok::with('subgroups')->get();

        $availableMedicines = Supply::where('quantity', '>', 0)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->paginate(10, ['*'], 'medicine_page');




        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        $citizens = $query->with(['subgroup', 'family.subgroup'])->paginate(10)->appends([
            'search' => $request->search,
            'purok' => $request->purok,
            'subgroup' => $request->subgroup,
            'age' => $request->age,
            'age_group' => $request->age_group,
            'sex' => $request->sex,
            'housing_status' => $request->housing_status,
            'pregnant' => $request->pregnant,
            'in_pills' => $request->in_pills,
            'include_deceased' => $request->include_deceased,
        ]);

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD COUNTS (Unrestricted full barangay totals)
        |--------------------------------------------------------------------------
        */
        $totalCitizens = (clone $summaryQuery)->count();
        $kids = (clone $summaryQuery)->where('Citizen_Age', '<=', 17)->count();
        $adults = (clone $summaryQuery)->whereBetween('Citizen_Age', [18, 59])->count();
        $seniors = (clone $summaryQuery)->where('Citizen_Age', '>=', 60)->count();

        $ordinalCitizens = citizens::with(['subgroup', 'family.subgroup'])->orderBy('id')->get();
        $subgroupOrdinals = [];
        $citizenDisplayIds = [];
        foreach ($ordinalCitizens as $ordinalCitizen) {
            $subgroup = $ordinalCitizen->subgroup ?: optional($ordinalCitizen->family)->subgroup;
            $subgroupKey = $subgroup ? (string) $subgroup->id : 'unassigned';
            $subgroupOrdinals[$subgroupKey] = ($subgroupOrdinals[$subgroupKey] ?? 0) + 1;
            $subgroupName = strtoupper($subgroup?->name ?? 'UNASSIGNED');
            $citizenDisplayIds[$ordinalCitizen->id] = str_pad((string) $subgroupOrdinals[$subgroupKey], 3, '0', STR_PAD_LEFT) . '-' . $subgroupName;
        }

        return view('bhw.citizen', compact(
            'citizens',
            'totalCitizens',
            'kids',
            'adults',
            'seniors',
            'puroks',
            'citizenDisplayIds'
        ));
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
            'Citizen_MName' => 'nullable|string|max:255',
            'Citizen_LName' => 'required|string|max:255',
            'Citizen_BirthDate' => 'required|date|before_or_equal:today',
            'Citizen_ContactNo' => 'nullable|string|max:50',
            'family_id' => 'nullable|exists:families,id',
            'Citizen_Purok' => 'nullable|string|max:255|required_without:family_id',
            'subgroup_id' => 'nullable|exists:purok_subgroups,id|required_without:family_id',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'Citizen_Sex' => 'required|string|max:255',
        ]);

        $family = null;
        if (!empty($validated['family_id'])) {
            $family = Family::with(['purok', 'subgroup'])->findOrFail($validated['family_id']);
            if (!$family->purok || !$family->subgroup) {
                return back()->withErrors(['family_id' => 'The selected family is missing its Purok or subgroup.'])->withInput();
            }
        } else {
            $manualSubgroup = PurokSubgroup::with('purok')->findOrFail($validated['subgroup_id']);
            if (!$manualSubgroup->purok || $manualSubgroup->purok->name !== $validated['Citizen_Purok']) {
                return back()->withErrors(['subgroup_id' => 'The selected subgroup must belong to the selected Purok.'])->withInput();
            }
        }

        $validated['Citizen_Age'] = Carbon::parse($validated['Citizen_BirthDate'])->age;

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('citizen-photos', 'public')
            : null;

        try {
            $citizen = DB::transaction(function () use ($validated, $photoPath, $request, $family) {
                $citizen = citizens::create([
                    'Citizen_FName' => $validated['Citizen_FName'],
                    'Citizen_MName' => $validated['Citizen_MName'] ?? null,
                    'Citizen_LName' => $validated['Citizen_LName'],
                    'Citizen_BirthDate' => $validated['Citizen_BirthDate'],
                    'Citizen_Age' => $validated['Citizen_Age'],
                    'Citizen_ContactNo' => $validated['Citizen_ContactNo'] ?? null,
                    'Citizen_Purok' => $family?->purok->name ?? $validated['Citizen_Purok'],
                    'subgroup_id' => $family?->subgroup->id ?? $validated['subgroup_id'],
                    'family_id' => $family?->id,
                    'qr_token' => (string) Str::uuid(),
                    'photo_path' => $photoPath,
                    'Citizen_Sex' => $validated['Citizen_Sex'] ?? null,
                ]);

                $password = $request->filled('password') 
                    ? Hash::make($request->password) 
                    : Hash::make('Barangay123!');

                User::create([
                    'name' => $validated['Citizen_FName'] . ' ' . $validated['Citizen_LName'],
                    'email' => $validated['email'],
                    'password' => $password,
                    'role' => 'citizen',
                    'citizen_id' => $citizen->id,
                    'must_change_password' => $request->filled('password') ? 0 : 1,
                ]);

                return $citizen;
            });
        } catch (\Throwable $e) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            throw $e;
        }

        $this->logActivity(
            'create',
            'citizen',
            $citizen->id,
            'Added new citizen and account: ' . $citizen->Citizen_FName . ' ' . $citizen->Citizen_LName
        );

        return redirect()
            ->route('citizenlist')
            ->with('success', 'Citizen account and health ID created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | HEALTH RECORD STORE
    |--------------------------------------------------------------------------
    */

    public function healthRecordIndex(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') abort(403);

        $search = $request->input('search');
        $purok = $request->input('purok');
        $query = $this->restrictToAssignedCitizens(citizens::with('healthRecords'));

        if ($purok && $purok !== 'all') {
            $query->where('Citizen_Purok', $purok);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Citizen_FName', 'LIKE', "%{$search}%")
                  ->orWhere('Citizen_LName', 'LIKE', "%{$search}%")
                  ->orWhereHas('healthRecords', function ($subQuery) use ($search) {
                      $subQuery->where('diagnosis', 'LIKE', "%{$search}%");
                  });
            });
        }

        $citizens = $query->paginate(10)->appends($request->all());
        $allCitizens = $this->restrictToAssignedCitizens(citizens::with('healthRecords'))->get();
        $heatmapData = [];

        foreach (Purok::whereNotNull('latitude')->whereNotNull('longitude')->get() as $purokRecord) {
            $purokKey = preg_replace('/[^0-9]/', '', (string) $purokRecord->name);
            $matchingCitizens = $allCitizens->filter(function ($citizen) use ($purokKey) {
                $dbPurok = preg_replace('/[^0-9]/', '', (string) $citizen->Citizen_Purok);
                return $dbPurok === (string) $purokKey;
            });
            $recordCount = $matchingCitizens->sum(fn ($citizen) => $citizen->healthRecords->count());

            if ($recordCount > 0) {
                $heatmapData[] = [
                    'location' => ['lat' => (float) $purokRecord->latitude, 'lng' => (float) $purokRecord->longitude],
                    'weight' => $recordCount * 3,
                    'purok' => $purokRecord->name,
                    'citizens_count' => $matchingCitizens->count(),
                    'records_count' => $recordCount,
                ];
            }
        }

        return view('bhw.healthrecord', compact('citizens', 'heatmapData'));
    }

    public function storeHealthRecord(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') abort(403);

        $validated = $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'diagnosis' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $record = HealthRecord::create([
            'citizen_id' => $validated['citizen_id'],
            'diagnosis' => $validated['diagnosis'],
            'notes' => $validated['notes'] ?? null,
        ]);

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'citizen_id' => $validated['citizen_id'],
            'health_record_id' => $record->id,
            'description' => 'Added health record: ' . $validated['diagnosis'],
        ]);

        $this->logActivity('create', 'health_record', $validated['citizen_id'], 'Added health record: ' . $validated['diagnosis']);
        return back()->with('success', 'Health record added successfully.');
    }

    public function storeMaintenance(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') abort(403);

        $validated = $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'medicine_source' => 'required|in:existing,custom',
            'existing_medicine' => 'nullable|required_if:medicine_source,existing|string|max:255|exists:supplies,name',
            'custom_medicine' => 'nullable|required_if:medicine_source,custom|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        MaintenanceRecord::create([
            'citizen_id' => $validated['citizen_id'],
            'medicine_name' => $validated['medicine_source'] === 'custom' ? $validated['custom_medicine'] : $validated['existing_medicine'],
            'dosage' => $validated['dosage'],
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'user_id' => Auth::id(),
        ]);

        $this->logActivity('create', 'maintenance', $validated['citizen_id'], 'Added maintenance medicine.');
        return back()->with('success', 'Maintenance medicine added successfully.');
    }

    public function healthIndex()
    {
        $citizens = citizens::paginate(10);
        $recentDiagnoses = HealthRecord::with('citizen')->latest()->take(5)->get();
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
        $citizen = citizens::with([
            'healthRecords' => fn($q) => $q->latest(),
            'maintenanceRecords' => fn($q) => $q->latest(),
            'subgroup',
            'family.subgroup',
        ])->findOrFail($id);

        $this->authorizeCitizenEdit($citizen);

        $availableMedicines = Supply::where('quantity', '>', 0)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->paginate(10, ['*'], 'medicine_page');

        $this->logActivity('view', 'citizen', $citizen->id, 'Viewed citizen health record');

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'view',
            'citizen_id' => $citizen->id,
            'health_record_id' => null,
            'description' => 'Viewed health records of citizen ID ' . $citizen->id,
        ]);

        return view('bhw.citizen_show', compact('citizen', 'availableMedicines'));
    }

    /*
    |--------------------------------------------------------------------------
    | CITIZEN DETAILS PAGE
    |--------------------------------------------------------------------------
    */
    public function citizendetails($id)
    {
        $citizen = citizens::with([
            'healthRecords' => fn ($query) => $query->latest(),
            'maintenanceRecords' => fn ($query) => $query->latest(),
            'vaccinations',
            'family.purok',
            'family.subgroup',
            'subgroup',
        ])->findOrFail($id);

        $canEdit = $this->canEditCitizen($citizen);
        $families = Family::with(['purok', 'subgroup'])
            ->orderBy('family_name')
            ->get();
        $puroks = Purok::with('subgroups')->get();

        // The citizen details Blade view uses this list in the medicine selector.
        $availableMedicines = Supply::where('quantity', '>', 0)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->paginate(10, ['*'], 'medicine_page');

        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen details page'
        );

        return view('bhw.citizendetails', compact(
            'citizen',
            'families',
            'puroks',
            'availableMedicines',
            'canEdit'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE CITIZEN (Restricted to Assigned BHW)
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $citizen = citizens::findOrFail($id);

        // Enforce subgroup assignment check
        $this->authorizeCitizenEdit($citizen);

        $this->logActivity('delete', 'citizen', $citizen->id, 'Deleted citizen');
        $citizen->delete();

        return redirect()->route('citizenlist')->with('success', 'Citizen deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE CITIZEN (Restricted to Assigned BHW)
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $citizen = citizens::findOrFail($id);

        // Enforce subgroup assignment check
        $this->authorizeCitizenEdit($citizen);

        $validated = $request->validate([
            'Citizen_FName' => 'required|string|max:255',
            'Citizen_MName' => 'nullable|string|max:255',
            'Citizen_LName' => 'required|string|max:255',
            'Citizen_BirthDate' => 'required|date|before_or_equal:today',
            'Citizen_ContactNo' => 'nullable|string|max:50',
            'Citizen_Purok' => 'nullable|string|max:255|required_without:family_id',
            'family_id' => 'nullable|exists:families,id',
            'subgroup_id' => 'nullable|exists:purok_subgroups,id|required_without:family_id',
            'Citizen_Height' => 'nullable|numeric|min:0',
            'Citizen_Weight' => 'nullable|numeric|min:0',
            'Citizen_Sex' => 'nullable|string|in:Male,Female',
            'Citizen_HousingStatus' => 'nullable|string|max:255',
            'Citizen_Pregnant' => 'nullable|boolean',
            'Citizen_InPills' => 'nullable|boolean',
            'Citizen_IsDeceased' => 'nullable|boolean',
        ]);

        $validated['Citizen_Age'] = Carbon::parse($validated['Citizen_BirthDate'])->age;

        $family = Family::with(['purok', 'subgroup'])->find($validated['family_id'] ?? $citizen->family_id);
        if ($family) {
            $validated['family_id'] = $family->id;
            $validated['Citizen_Purok'] = $family->purok->name;
            $validated['subgroup_id'] = $family->subgroup->id;
        } else {
            $manualSubgroup = PurokSubgroup::with('purok')->findOrFail($validated['subgroup_id']);
            if (!$manualSubgroup->purok || $manualSubgroup->purok->name !== $validated['Citizen_Purok']) {
                return back()->withErrors(['subgroup_id' => 'The selected subgroup must belong to the selected Purok.'])->withInput();
            }
        }

        $citizen->update($validated);

        $this->logActivity('update', 'citizen', $citizen->id, 'Updated citizen details');

        return redirect()->back()->with('success', 'Citizen updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | EXPORT CITIZENS
    |--------------------------------------------------------------------------
    */
    public function export()
    {
        $this->logAdminActivity('citizens.exported', 'Exported citizen records to Excel.', 'data');

        return Excel::download(
            new CitizensExport(),
            'citizen_records_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT CITIZENS
    |--------------------------------------------------------------------------
    */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls|max:2048',
        ]);

        try {
            $file = $request->file('file');
            $import = new CitizensImport();
            Excel::import($import, $file);

            $this->logActivity('import', 'citizen', null, 'Imported citizens via CSV/Excel spreadsheet');
            $this->logAdminActivity('citizens.imported', 'Imported citizen records from Excel.', 'data');

            return redirect()->route('citizenlist')->with(
                'success',
                "Citizens imported successfully. {$import->createdAccounts} citizen accounts were created with the default temporary password."
            );
        } catch (\Exception $e) {
            $this->logAuditEvent('citizens.import_failed', 'Citizen Excel import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | BHW ELECTRONIC CARD
    |--------------------------------------------------------------------------
    */
    public function showElectronicCard($id)
    {
        $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])->findOrFail($id);

        return view('bhw.ecard', compact('citizen'));
    }

    public function showElectronicCardByToken(string $token)
    {
        $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('bhw.ecard', compact('citizen'));
    }

    public function makeCitizenQrImage(citizens $citizen): string
    {
        if (!$citizen->qr_token) {
            $citizen->qr_token = (string) Str::uuid();
            $citizen->save();
        }

        // Use a stable public Health ID URL. The public page does not expose
        // medical information; authorized providers must sign in to view records.
        $url = route('health-id.public', [
            'token' => $citizen->qr_token,
        ]);

        // Generate the QR locally instead of sending the citizen token to a
        // third-party QR-generation service.
        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Low,
            size: 180,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
        );

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);

        return 'data:image/svg+xml;base64,' . base64_encode($result->getString());
    }

    /*
    |--------------------------------------------------------------------------
    | STORE VACCINATION RECORD
    |--------------------------------------------------------------------------
    */

    public function storeVaccinationRecord(Request $request)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') abort(403);

        $validated = $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'vaccine_name' => 'required|string|max:255',
            'date_administered' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        VaccinationRecord::create([
            'citizen_id' => $validated['citizen_id'],
            'vaccine_name' => $validated['vaccine_name'],
            'dose_number' => $validated['notes'] ?: 'Recorded dose',
            'date_administered' => $validated['date_administered'],
            'administered_by' => Auth::user()->name,
        ]);
        $this->logActivity('create', 'vaccination', $validated['citizen_id'], 'Added vaccination: ' . $validated['vaccine_name']);

        return back()->with('success', 'Vaccination added successfully.');
    }

    public function storeVaccination(Request $request, $id = null)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') abort(403);
        $citizenId = $id ?? $request->input('citizen_id');
        validator(['citizen_id' => $citizenId], ['citizen_id' => 'required|exists:citizens,id'])->validate();
        $citizen = citizens::findOrFail($citizenId);
        if (!$this->canEditCitizen($citizen) || (int) $citizen->Citizen_IsDeceased === 1) {
            abort(403, 'You are not authorized to update this citizen vaccination record.');
        }
        $validated = $request->validate([
            'vaccine_name' => 'required|string|max:255',
            'dose_number' => 'required|string|max:100',
            'date_administered' => 'required|date',
            'administered_by' => 'required|string|max:255',
        ]);

        $validated['citizen_id'] = $citizenId;

        VaccinationRecord::create($validated);

        $this->logActivity(
            'create',
            'vaccination',
            $citizenId,
            'Administered vaccine (' . $validated['vaccine_name'] . ' - ' . $validated['dose_number'] . ') to citizen ID ' . $citizenId
        );

        return back()->with('success', 'Vaccination record added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE MEDICATION RECORD
    |--------------------------------------------------------------------------
    */
    public function storeMedication(Request $request, $id)
    {
        $validated = $request->validate([
            'medicine_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'quantity_dispensed' => 'required|integer|min:1',
            'date_dispensed' => 'required|date',
        ]);

        $validated['citizen_id'] = $citizenId;

        MedicationRecord::create($validated);

        $this->logActivity(
            'create',
            'medication',
            $id,
            'Dispensed medication (' . $validated['medicine_name'] . ' x' . $validated['quantity_dispensed'] . ') to citizen ID ' . $id
        );

        return back()->with('success', 'Medication log added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | CITIZEN SELF-SERVICE E-CARD
    |--------------------------------------------------------------------------
    */
    public function citizenViewECard()
    {
        $user = Auth::user();

        if (!$user->citizen_id) {
            abort(403, 'This account is not linked to a citizen profile.');
        }

        $citizen = citizens::with(['vaccinations', 'medications', 'healthRecords'])
            ->where('id', $user->citizen_id)
            ->firstOrFail();

        $qrImage = $this->makeCitizenQrImage($citizen);

        return view('citizen.ecard', [
            'citizen' => $citizen,
            'qrImage' => $qrImage,
        ]);
    }
}