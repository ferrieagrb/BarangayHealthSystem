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
use App\Models\VaccinationSchedule;
use App\Models\MaintenanceRecord;
use App\Models\MedicationRecord;
use App\Models\Supply;
use App\Models\Family;
use App\Models\Purok;
use App\Exports\CitizensExport;
use App\Imports\CitizensImport;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\PurokSubgroup;
use App\Models\UserPermission;
use App\Models\SystemSetting;
use App\Services\BhwCitizenAccess;
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
        if (!$user || $user->role !== 'bhw') {
            return true;
        }

        return app(BhwCitizenAccess::class)->canAccessCitizen($user, $citizen);
    }

    private function restrictToAssignedCitizens($query)
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'bhw') {
            return $query;
        }

        return app(BhwCitizenAccess::class)->scopeCitizens($query, $user);
    }

    private function filterByAgeRange(Builder $query, int $minimumAge, ?int $maximumAge = null): void
    {
        $today = Carbon::today();
        $latestBirthDate = $today->copy()->subYearsNoOverflow($minimumAge)->toDateString();
        $query->whereDate('Citizen_BirthDate', '<=', $latestBirthDate);

        if ($maximumAge !== null) {
            $earliestBirthDate = $today->copy()->subYearsNoOverflow($maximumAge + 1)->toDateString();
            $query->whereDate('Citizen_BirthDate', '>', $earliestBirthDate);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CITIZEN LIST PAGE (All BHWs can view all citizens)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
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

        $vaccineFilters = (array) $request->input('health_filter_vaccine', []);
        if ($vaccineFilters) {
            $vaccineTerms = [
                'bcg' => ['bcg'], 'hepb' => ['hepatitis b', 'hepb'], 'penta' => ['pentavalent', 'penta'],
                'opv' => ['oral polio', 'opv'], 'ipv' => ['inactivated polio', 'ipv'], 'pcv' => ['pneumococcal', 'pcv'],
                'mmr' => ['measles, mumps', 'mmr'], 'mr' => ['measles-rubella', 'mr'], 'td' => ['tetanus', 'td'], 'hpv' => ['human papillomavirus', 'hpv'],
            ];
            $query->whereHas('vaccinations', function ($vaccinationQuery) use ($vaccineFilters, $vaccineTerms) {
                $vaccinationQuery->where(function ($builder) use ($vaccineFilters, $vaccineTerms) {
                    foreach ($vaccineFilters as $filter) {
                        foreach ($vaccineTerms[$filter] ?? [$filter] as $term) {
                            $builder->orWhere('vaccine_name', 'like', "%{$term}%");
                        }
                    }
                });
            });
        }

        if ($request->boolean('filter_deceased')) {
            $query->where('Citizen_IsDeceased', 1);
        } else {
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
            if ($search !== '') {
                $searchTerms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [$search];
                $query->where(function ($q) use ($search, $searchTerms) {
                    $q->where(function ($nameQuery) use ($searchTerms) {
                        foreach ($searchTerms as $term) {
                            $nameQuery->where(function ($termQuery) use ($term) {
                                $termQuery->where('Citizen_FName', 'like', '%' . $term . '%')
                                    ->orWhere('Citizen_MName', 'like', '%' . $term . '%')
                                    ->orWhere('Citizen_LName', 'like', '%' . $term . '%');
                            });
                        }
                    })
                      ->orWhere('id', $search)
                      ->orWhereHas('healthRecords', function ($q2) use ($search) {
                          $q2->where('diagnosis', 'like', '%' . $search . '%');
                      });
                });
            }
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

                $this->filterByAgeRange($query, $minAge, $maxAge);
            } elseif (is_numeric($age)) {
                $this->filterByAgeRange($query, (int) $age, (int) $age);
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
                $this->filterByAgeRange($query, 0, 17);
            } elseif ($ageGroup === 'adult') {
                $this->filterByAgeRange($query, 18, 59);
            } elseif ($ageGroup === 'senior') {
                $this->filterByAgeRange($query, 60);
            }
        }

        $puroks = Purok::with('subgroups')->get();

        $availableMedicines = Supply::where('quantity', '>', 0)
            ->select('name')
            ->distinct()
            ->orderBy('name')
            ->paginate(10, ['*'], 'medicine_page');

        $sort = $request->input('sort');
        if ($sort === 'Youngest to Oldest') {
            $query->orderByDesc('Citizen_BirthDate')
                ->orderBy('Citizen_FName')
                ->orderBy('Citizen_LName');
        } elseif ($sort === 'Oldest to Youngest') {
            $query->orderBy('Citizen_BirthDate')
                ->orderBy('Citizen_FName')
                ->orderBy('Citizen_LName');
        } else {
            $query->orderBy('Citizen_FName')
                ->orderBy('Citizen_LName');
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */
        $citizens = $query->with(['subgroup', 'family.subgroup'])
            ->paginate(10)
            ->withQueryString();
        foreach ($citizens as $citizen) {
            $citizen->setAttribute('Citizen_Age', Carbon::parse($citizen->Citizen_BirthDate)->age);
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD COUNTS (Unrestricted full barangay totals)
        |--------------------------------------------------------------------------
        */
        $totalCitizens = (clone $summaryQuery)->count();
        $kidsQuery = clone $summaryQuery;
        $this->filterByAgeRange($kidsQuery, 0, 17);
        $kids = $kidsQuery->count();
        $adultsQuery = clone $summaryQuery;
        $this->filterByAgeRange($adultsQuery, 18, 59);
        $adults = $adultsQuery->count();
        $seniorsQuery = clone $summaryQuery;
        $this->filterByAgeRange($seniorsQuery, 60);
        $seniors = $seniorsQuery->count();
        $latestPeriodStart = now()->subDays(13)->startOfDay();
        $previousPeriodStart = now()->subDays(27)->startOfDay();
        $previousPeriodEnd = now()->subDays(14)->endOfDay();

        $rate = function (int $current, int $previous): array {
            if ($previous === 0) {
                return [
                    'value' => $current > 0 ? '+100%' : 'No change',
                    'state' => $current > 0 ? 'up' : 'flat',
                    'icon' => $current > 0 ? '▲' : '•',
                ];
            }

            $change = round((($current - $previous) / $previous) * 100, 1);

            return [
                'value' => $change > 0 ? '+' . abs($change) . '%' : ($change < 0 ? '-' . abs($change) . '%' : 'No change'),
                'state' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'flat'),
                'icon' => $change > 0 ? '▲' : ($change < 0 ? '▼' : '•'),
            ];
        };

        $periodCount = function ($baseQuery, $start, $end, ?string $group = null): int {
            $query = (clone $baseQuery)->whereBetween('created_at', [$start, $end]);

            if ($group === 'kids') {
                $this->filterByAgeRange($query, 0, 17);
            } elseif ($group === 'adults') {
                $this->filterByAgeRange($query, 18, 59);
            } elseif ($group === 'seniors') {
                $this->filterByAgeRange($query, 60);
            }

            return $query->count();
        };

        $citizenTrends = [
            'total' => $rate(
                $periodCount($summaryQuery, $latestPeriodStart, now()),
                $periodCount($summaryQuery, $previousPeriodStart, $previousPeriodEnd)
            ),
            'kids' => $rate(
                $periodCount($summaryQuery, $latestPeriodStart, now(), 'kids'),
                $periodCount($summaryQuery, $previousPeriodStart, $previousPeriodEnd, 'kids')
            ),
            'adults' => $rate(
                $periodCount($summaryQuery, $latestPeriodStart, now(), 'adults'),
                $periodCount($summaryQuery, $previousPeriodStart, $previousPeriodEnd, 'adults')
            ),
            'seniors' => $rate(
                $periodCount($summaryQuery, $latestPeriodStart, now(), 'seniors'),
                $periodCount($summaryQuery, $previousPeriodStart, $previousPeriodEnd, 'seniors')
            ),
        ];

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

        $exportFieldDefinitions = $this->exportFieldDefinitions();

        return view('bhw.citizen', compact(
            'citizens',
            'totalCitizens',
            'kids',
            'adults',
            'seniors',
            'citizenTrends',
            'puroks',
            'citizenDisplayIds',
            'exportFieldDefinitions'
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
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $citizen = citizens::findOrFail($request->input('citizen_id'));
        if (!$this->canEditCitizen($citizen) || (int) $citizen->Citizen_IsDeceased === 1) {
            abort(403, 'You are not authorized to add a diagnosis to this citizen record.');
        }

        $validated = $request->validate([
            'citizen_id' => 'required|exists:citizens,id',
            'diagnosis' => 'required|string|max:255',
            'record_date' => 'required|date',
            'comments' => 'nullable|string|max:2000',
            'severity' => 'nullable|in:Low / Mild,Moderate,High / Severe,Critical,Resolved',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:1|max:300',
            'temperature' => 'nullable|numeric|min:25|max:45',
            'blood_pressure' => 'nullable|string|max:20',
        ]);

        $weight = isset($validated['weight']) && $validated['weight'] !== '' ? (float) $validated['weight'] : null;
        $height = isset($validated['height']) && $validated['height'] !== '' ? (float) $validated['height'] : null;
        $bmi = ($weight !== null && $height !== null && $height > 0)
            ? round($weight / (($height / 100) ** 2), 1)
            : null;

        $record = HealthRecord::create([
            'citizen_id' => $validated['citizen_id'],
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

        $profileUpdates = [];
        if ($weight !== null) $profileUpdates['Citizen_Weight'] = $weight;
        if ($height !== null) $profileUpdates['Citizen_Height'] = $height;
        if ($profileUpdates) {
            $citizen->forceFill($profileUpdates)->save();
        }

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'create',
            'citizen_id' => $validated['citizen_id'],
            'health_record_id' => $record->id,
            'description' => 'Added health record: ' . $validated['diagnosis'],
        ]);

        $this->logActivity('create', 'health_record', $validated['citizen_id'], 'Added health record: ' . $validated['diagnosis']);

        return back()->with('success', 'Diagnosis added successfully.');
    }

    public function deleteHealthRecord($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $record = HealthRecord::with('citizen')->findOrFail($id);
        $citizen = $record->citizen;

        if (!$citizen || !$this->canEditCitizen($citizen) || (int) $citizen->Citizen_IsDeceased === 1) {
            abort(403, 'You are not authorized to delete this diagnosis record.');
        }

        $diagnosis = $record->diagnosis;
        $citizenId = $record->citizen_id;
        $recordId = $record->id;

        HealthRecordActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'delete',
            'citizen_id' => $citizenId,
            'health_record_id' => $recordId,
            'description' => 'Deleted health record: ' . $diagnosis,
        ]);

        $record->delete();
        $this->logActivity('delete', 'health_record', $citizenId, 'Deleted health record: ' . $diagnosis);

        return back()->with('success', 'Diagnosis record deleted successfully.');
    }

    public function deleteVaccinationRecord($citizenId, $recordId)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $citizen = citizens::findOrFail($citizenId);
        if (!$this->canEditCitizen($citizen) || (int) $citizen->Citizen_IsDeceased === 1) {
            abort(403, 'You are not authorized to delete this vaccination record.');
        }

        $record = VaccinationRecord::where('citizen_id', $citizen->id)
            ->findOrFail($recordId);

        $description = 'Deleted vaccination: ' . $record->vaccine_name
            . ' (' . $record->dose_number . ')';
        $record->delete();

        $this->logActivity('delete', 'vaccination', $citizen->id, $description);

        return back()->with('success', 'Vaccination record deleted successfully.');
    }

    public function deleteMaintenanceRecord($id)
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $record = MaintenanceRecord::with('citizen')->findOrFail($id);
        $citizen = $record->citizen;

        if (!$citizen || !$this->canEditCitizen($citizen) || (int) $citizen->Citizen_IsDeceased === 1) {
            abort(403, 'You are not authorized to delete this maintenance medicine record.');
        }

        $citizenId = $citizen->id;
        $medicineName = $record->medicine_name;
        $record->delete();

        $this->logActivity('delete', 'maintenance', $citizenId, 'Deleted maintenance medicine: ' . $medicineName);

        return back()->with('success', 'Maintenance medicine deleted successfully.');
    }

    public function storeMaintenance(Request $request)
    {
        $user = Auth::user();

        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $validated = $request->validate([
            'citizen_id'         => 'required|integer|exists:citizens,id',
            'medicine_name'      => 'required|string|max:255',
            'quantity_dispensed' => 'required|integer|min:1|max:100000',
            'dosage'             => 'required|numeric|min:0.01|max:10000',
            'frequency_times'    => 'required|string|in:Once,Twice,Three times',
            'frequency_period'   => 'required|string|in:per day,per week,every other day',
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'notes'              => 'nullable|string|max:2000',
        ]);

        $citizen = citizens::findOrFail($validated['citizen_id']);
        $this->authorizeCitizenEdit($citizen);

        $medicineName = trim($validated['medicine_name']);

        if ($medicineName === '') {
            return back()
                ->withErrors(['medicine_name' => 'Please enter a medicine name.'])
                ->withInput();
        }

        $combinedFrequency = $validated['frequency_times'] . ' ' . $validated['frequency_period'];

        $record = MaintenanceRecord::create([
            'citizen_id'         => $citizen->id,
            'medicine_name'      => $medicineName,
            'quantity_dispensed' => (int) $validated['quantity_dispensed'],
            'dosage'             => $validated['dosage'],
            'frequency'          => $combinedFrequency,
            'start_date'         => $validated['start_date'],
            'end_date'           => $validated['end_date'] ?? null,
            'notes'              => isset($validated['notes']) ? trim($validated['notes']) : null,
            'user_id'            => $user->id,
        ]);

        $this->logActivity(
            'create',
            'maintenance',
            $citizen->id,
            'Added maintenance medicine: ' . $medicineName
                . ' (quantity ' . $record->quantity_dispensed
                . ', dosage ' . $record->dosage . ', frequency ' . $record->frequency . ').'
        );

        return redirect()
            ->back()
            ->with('success', 'Maintenance medicine added successfully.');
    }

    public function healthIndex()
    {
        $citizens = citizens::paginate(10);
        $recentDiagnoses = HealthRecord::with('citizen')->latest()->take(5)->get();
        $totalRecords = HealthRecord::count();

        HealthRecordActivityLog::create([
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
            'medications' => fn ($query) => $query->latest(),
            'vaccinations',
            'vaccinationSchedules' => fn ($query) => $query->where('status', 'scheduled')->orderBy('scheduled_date'),
            'family.purok',
            'family.subgroup',
            'subgroup',
        ])->findOrFail($id);

        $canEdit = $this->canEditCitizen($citizen);
        $families = Family::with(['purok', 'subgroup'])
            ->orderBy('family_name')
            ->get();
        $puroks = Purok::with('subgroups')->get();

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

        $vaccinationSchedulingEnabled = SystemSetting::boolean('vaccination_scheduling_enabled', true);

        return view('bhw.citizendetails', compact(
            'citizen',
            'families',
            'puroks',
            'availableMedicines',
            'canEdit',
            'vaccinationSchedulingEnabled'
        ));
    }

    public function administerVaccinationSchedule(Request $request, $id, $scheduleId)
    {
        abort_unless(SystemSetting::boolean('vaccination_scheduling_enabled', true), 404);

        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $citizen = citizens::findOrFail($id);
        $this->authorizeCitizenEdit($citizen);

        $schedule = VaccinationSchedule::where('id', $scheduleId)
            ->where('citizen_id', $citizen->id)
            ->where('status', 'scheduled')
            ->firstOrFail();

        $validated = $request->validate([
            'date_administered' => 'required|date|before_or_equal:today',
        ]);

        VaccinationRecord::create([
            'citizen_id' => $citizen->id,
            'vaccine_name' => $schedule->vaccine_name,
            'dose_number' => $schedule->dose_number ?: 'Scheduled dose',
            'date_administered' => $validated['date_administered'],
            'administered_by' => $user->name,
        ]);

        $schedule->update(['status' => 'completed']);

        $this->logActivity(
            'update',
            'vaccination_schedule',
            $citizen->id,
            'Marked scheduled ' . $schedule->vaccine_name . ' vaccination as administered'
        );

        return back()->with('success', 'Vaccination marked as administered and added to immunization history.');
    }

    public function resetCitizenPassword(Request $request, $id)
    {
        $user = auth()->user();
        abort_unless($user && $user->hasWriteAccess('citizen_password_reset'), 403, 'You do not have permission to reset citizen passwords.');

        $citizen = citizens::findOrFail($id);
        $this->authorizeCitizenEdit($citizen);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $citizenAccount = User::where('citizen_id', $citizen->id)
            ->where('role', 'citizen')
            ->firstOrFail();

        $citizenAccount->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
            'failed_login_attempts' => 0,
            'is_locked' => false,
        ]);

        $this->logActivity(
            'password_reset',
            'citizen',
            $citizen->id,
            'Reset citizen account password; citizen must change it at next login'
        );

        return back()->with('success', 'Citizen password reset successfully. The citizen must change it at next login.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE CITIZEN (Restricted to Assigned BHW)
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $citizen = citizens::findOrFail($id);

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
    private function exportCitizenQuery(Request $request)
    {
        $query = citizens::query();
        $requestedFields = (array) $request->input('fields', []);
        if (collect($requestedFields)->contains(
            fn ($field) => is_string($field) && str_starts_with($field, 'immunization_')
        )) {
            $query->with('vaccinations');
        }

        if ($request->filled('filter_sex') && $request->filter_sex !== 'all') {
            $query->where('Citizen_Sex', $request->filter_sex);
        }

        if ($request->filled('filter_housing')) {
            $query->whereIn('Citizen_HousingStatus', (array) $request->filter_housing);
        }

        if ($request->filled('filter_health')) {
            $healthValues = (array) $request->filter_health;
            $query->when(in_array('pregnant', $healthValues), fn ($builder) => $builder->where('Citizen_Pregnant', 1));
            $query->when(in_array('in_pills', $healthValues), fn ($builder) => $builder->where('Citizen_InPills', 1));
        }

        if ($request->boolean('filter_deceased')) {
            $query->where('Citizen_IsDeceased', 1);
        } else {
            $query->where(function ($builder) {
                $builder->whereNull('Citizen_IsDeceased')->orWhere('Citizen_IsDeceased', 0);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            if ($search !== '') {
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
                    })->orWhere('id', $search)
                        ->orWhereHas('healthRecords', fn ($healthQuery) => $healthQuery->where('diagnosis', 'like', "%{$search}%"));
                });
            }
        }

        if ($request->filled('purok') && $request->purok !== 'all') {
            $purokValue = $request->purok;
            $purokRecord = Purok::find($purokValue);
            $query->where(function ($builder) use ($purokRecord, $purokValue) {
                $builder->where('Citizen_Purok', $purokRecord?->name ?: $purokValue)
                    ->orWhere('Citizen_Purok', $purokValue);
            });
        }

        if ($request->filled('subgroup') && $request->subgroup !== 'all') {
            $subgroupId = $request->subgroup;
            $query->where(function ($builder) use ($subgroupId) {
                $builder->where('subgroup_id', $subgroupId)
                    ->orWhereHas('family', fn ($familyQuery) => $familyQuery->where('subgroup_id', $subgroupId));
            });
        }

        if ($request->filled('age')) {
            $age = trim($request->age);
            if (preg_match('/^(\d+)\s*-\s*(\d+)$/', $age, $matches)) {
                $this->filterByAgeRange(
                    $query,
                    min((int) $matches[1], (int) $matches[2]),
                    max((int) $matches[1], (int) $matches[2])
                );
            } elseif (is_numeric($age)) {
                $this->filterByAgeRange($query, (int) $age, (int) $age);
            }
        }

        if ($request->filled('age_group')) {
            if ($request->age_group === 'kid') {
                $this->filterByAgeRange($query, 0, 17);
            } elseif ($request->age_group === 'adult') {
                $this->filterByAgeRange($query, 18, 59);
            } elseif ($request->age_group === 'senior') {
                $this->filterByAgeRange($query, 60);
            }
        }

        $vaccineFilters = (array) $request->input('health_filter_vaccine', []);
        if ($vaccineFilters) {
            $vaccineTerms = [
                'bcg' => ['bcg'], 'hepb' => ['hepatitis b', 'hepb'], 'penta' => ['pentavalent', 'penta'],
                'opv' => ['oral polio', 'opv'], 'ipv' => ['inactivated polio', 'ipv'], 'pcv' => ['pneumococcal', 'pcv'],
                'mmr' => ['measles, mumps', 'mmr'], 'mr' => ['measles-rubella', 'mr'], 'td' => ['tetanus', 'td'], 'hpv' => ['human papillomavirus', 'hpv'],
            ];
            $query->whereHas('vaccinations', function ($vaccinationQuery) use ($vaccineFilters, $vaccineTerms) {
                $vaccinationQuery->where(function ($builder) use ($vaccineFilters, $vaccineTerms) {
                    foreach ($vaccineFilters as $filter) {
                        foreach ($vaccineTerms[$filter] ?? [$filter] as $term) {
                            $builder->orWhere('vaccine_name', 'like', "%{$term}%");
                        }
                    }
                });
            });
        }

        return match ($request->input('sort')) {
            'Youngest to Oldest' => $query->orderByDesc('Citizen_BirthDate')->orderBy('Citizen_FName')->orderBy('Citizen_LName'),
            'Oldest to Youngest' => $query->orderBy('Citizen_BirthDate')->orderBy('Citizen_FName')->orderBy('Citizen_LName'),
            default => $query->orderBy('Citizen_FName')->orderBy('Citizen_LName'),
        };
    }

    private function exportFilterSummary(Request $request): array
    {
        $filters = [];

        if ($request->filled('search')) {
            $filters[] = 'Search: ' . trim($request->search);
        }
        if ($request->filled('purok') && $request->purok !== 'all') {
            $purok = Purok::find($request->purok);
            $filters[] = 'Purok: ' . ($purok?->name ?: $request->purok);
        }
        if ($request->filled('subgroup') && $request->subgroup !== 'all') {
            $subgroup = PurokSubgroup::find($request->subgroup);
            $filters[] = 'Subgroup: ' . ($subgroup?->name ?: $request->subgroup);
        }
        if ($request->filled('age')) {
            $filters[] = 'Age: ' . trim($request->age);
        }
        if ($request->filled('age_group')) {
            $filters[] = 'Age group: ' . ucfirst($request->age_group);
        }
        if ($request->filled('filter_sex') && $request->filter_sex !== 'all') {
            $filters[] = 'Gender: ' . $request->filter_sex;
        }
        if ($request->filled('filter_housing')) {
            $filters[] = 'Housing: ' . implode(', ', (array) $request->filter_housing);
        }
        if ($request->filled('filter_health')) {
            $filters[] = 'Health: ' . implode(', ', (array) $request->filter_health);
        }
        if ($request->filled('health_filter_vaccine')) {
            $filters[] = 'Vaccines: ' . implode(', ', (array) $request->health_filter_vaccine);
        }

        $filters[] = $request->boolean('filter_deceased')
            ? 'Deceased citizens only'
            : 'Excludes deceased citizens';

        return $filters;
    }

    private function exportFieldDefinitions(): array
    {
        return [
            'id' => 'Citizen ID',
            'Citizen_FName' => 'First Name',
            'Citizen_MName' => 'Middle Name',
            'Citizen_LName' => 'Last Name',
            'Citizen_BirthDate' => 'Birth Date',
            'Citizen_ContactNo' => 'Contact Number',
            'Citizen_Purok' => 'Purok',
            'family_id' => 'Family ID',
            'subgroup_id' => 'Subgroup ID',
            'Citizen_Age' => 'Age',
            'Citizen_Sex' => 'Gender',
            'Citizen_Height' => 'Height',
            'Citizen_Weight' => 'Weight',
            'Citizen_HousingStatus' => 'Housing Status',
            'Citizen_Pregnant' => 'Pregnant',
            'Citizen_InPills' => 'Taking Pills',
            'Citizen_IsDeceased' => 'Deceased',
            'immunization_bcg' => 'BCG Recorded',
            'immunization_hepb' => 'Hepatitis B (HepB) Recorded',
            'immunization_penta' => 'Pentavalent (Penta) Recorded',
            'immunization_opv' => 'Oral Polio Vaccine (OPV) Recorded',
            'immunization_ipv' => 'Inactivated Polio Vaccine (IPV) Recorded',
            'immunization_pcv' => 'Pneumococcal Conjugate Vaccine (PCV) Recorded',
            'immunization_mmr' => 'Measles, Mumps, Rubella (MMR) Recorded',
            'immunization_mr' => 'Measles-Rubella (MR) Recorded',
            'immunization_td' => 'Tetanus-Diphtheria (Td) Recorded',
            'immunization_hpv' => 'Human Papillomavirus (HPV) Recorded',
        ];
    }

    private function selectedExportFields(Request $request): array
    {
        $availableFields = $this->exportFieldDefinitions();
        $requestedFields = $request->has('fields')
            ? $request->input('fields', [])
            : array_keys($availableFields);
        $selectedFields = array_values(array_intersect((array) $requestedFields, array_keys($availableFields)));

        abort_if(!$selectedFields, 422, 'Select at least one citizen detail to export.');

        return $selectedFields;
    }

    public function export(Request $request)
    {
        $selectedFields = $this->selectedExportFields($request);
        $this->logAdminActivity('citizens.exported', 'Exported citizen records to Excel.', 'data');

        return Excel::download(
            new CitizensExport($this->exportCitizenQuery($request), $this->exportFilterSummary($request), $selectedFields, $this->exportFieldDefinitions()),
            'citizen_records_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $citizens = $this->exportCitizenQuery($request)->get();
        $filterSummary = $this->exportFilterSummary($request);
        $fieldDefinitions = $this->exportFieldDefinitions();
        $selectedFields = $this->selectedExportFields($request);

        $this->logAdminActivity('citizens.exported_pdf', 'Exported citizen records to PDF.', 'data');

        return Pdf::loadView('exports.citizens-pdf', compact('citizens', 'filterSummary', 'fieldDefinitions', 'selectedFields'))
            ->setPaper('a4', 'landscape')
            ->download('citizen_records_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORT CITIZENS
    |--------------------------------------------------------------------------
    */
    public function import(Request $request)
    {
        set_time_limit(300);

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
            \Illuminate\Support\Facades\Log::error('Citizen Import Error: ' . $e->getMessage() . ' on line ' . $e->getLine());
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
        $citizen = citizens::with([
            'vaccinations',
            'vaccinationSchedules' => fn ($query) => $query->where('status', 'scheduled')->orderBy('scheduled_date'),
            'medications',
            'healthRecords',
            'maintenanceRecords' => fn ($query) => $query->latest(),
        ])->findOrFail($id);

        $this->authorizeCitizenEdit($citizen);
        $qrImage = $this->makeCitizenQrImage($citizen);

        return view('bhw.ecard', [
            'citizen' => $citizen,
            'qrImage' => $qrImage,
            'vaccinationSchedulingEnabled' => SystemSetting::boolean('vaccination_scheduling_enabled', true),
        ]);
    }

    public function scheduleVaccination(Request $request)
    {
        abort_unless(SystemSetting::boolean('vaccination_scheduling_enabled', true), 404);

        $user = Auth::user();
        if (!$user || $user->role !== 'bhw') {
            abort(403);
        }

        $validated = $request->validate([
            'schedules' => 'required|array|min:1',
            'schedules.*.citizen_id' => 'required|exists:citizens,id',
            'schedules.*.vaccine_name' => 'required|string|max:255',
            'schedules.*.dose_number' => 'nullable|string|max:100',
            'schedules.*.scheduled_date' => 'required|date|after_or_equal:today',
            'schedules.*.notes' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($validated, $user) {
            foreach ($validated['schedules'] as $scheduleData) {
                $citizen = citizens::findOrFail($scheduleData['citizen_id']);
                $this->authorizeCitizenEdit($citizen);

                VaccinationSchedule::create([
                    'citizen_id' => $citizen->id,
                    'vaccine_name' => $scheduleData['vaccine_name'],
                    'dose_number' => $scheduleData['dose_number'] ?? null,
                    'scheduled_date' => $scheduleData['scheduled_date'],
                    'notes' => $scheduleData['notes'] ?? null,
                    'status' => 'scheduled',
                    'scheduled_by' => $user->id,
                ]);

                $this->logActivity('create', 'vaccination_schedule', $citizen->id, 'Scheduled ' . $scheduleData['vaccine_name'] . ' vaccination');
            }
        });

        return back()->with('success', count($validated['schedules']) . ' vaccination schedule(s) added successfully.');
    }

    public function showElectronicCardByToken(string $token)
    {
        $citizen = citizens::with([
            'vaccinations',
            'vaccinationSchedules' => fn ($query) => $query->where('status', 'scheduled')->orderBy('scheduled_date'),
            'medications',
            'healthRecords',
            'maintenanceRecords' => fn ($query) => $query->latest(),
        ])->where('qr_token', $token)->firstOrFail();
        $qrImage = $this->makeCitizenQrImage($citizen);

        return view('bhw.ecard', [
            'citizen' => $citizen,
            'qrImage' => $qrImage,
            'vaccinationSchedulingEnabled' => SystemSetting::boolean('vaccination_scheduling_enabled', true),
        ]);
    }

    public function makeCitizenQrImage(citizens $citizen): string
    {
        if (!$citizen->qr_token) {
            $citizen->qr_token = (string) Str::uuid();
            $citizen->save();
        }

        $url = request()->getSchemeAndHttpHost() . route('health-id.public', [
            'token' => $citizen->qr_token,
        ], false);

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

        $citizen = citizens::with([
                'vaccinations',
            'vaccinationSchedules' => fn ($query) => $query->where('status', 'scheduled')->orderBy('scheduled_date'),
                'medications',
                'healthRecords',
                'maintenanceRecords' => fn ($query) => $query->latest(),
            ])
            ->where('id', $user->citizen_id)
            ->firstOrFail();

        $qrImage = $this->makeCitizenQrImage($citizen);

        return view('citizen.ecard', [
            'citizen' => $citizen,
            'qrImage' => $qrImage,
            'vaccinationSchedulingEnabled' => SystemSetting::boolean('vaccination_scheduling_enabled', true),
        ]);
    }
}