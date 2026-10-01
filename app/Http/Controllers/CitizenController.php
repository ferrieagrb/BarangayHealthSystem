<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\CitizenActivityLog;
use App\Models\HealthRecordActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
            $query->where('Citizen_Purok', $request->purok);
        }

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
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE CITIZEN & USER ACCOUNT
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        // Validate citizen details and login credentials
        $request->validate([
            'Citizen_FName'     => 'required|string|max:255',
            'Citizen_LName'     => 'required|string|max:255',
            'Citizen_BirthDate' => 'required|date',
            'Citizen_Age'       => 'required|integer',
            'Citizen_ContactNo' => 'nullable|string|max:50',
            'Citizen_Purok'     => 'required|string',
            'email'             => 'required|email|unique:users,email',
            'password'          => 'required|string|min:6',
        ]);

        $citizen = null;

        // Transaction ensures database integrity if any part fails
        DB::transaction(function () use ($request, &$citizen) {
            // 1. Create the User login account
            $user = User::create([
                'name'     => $request->Citizen_FName . ' ' . $request->Citizen_LName,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'citizen', // Ensures proper routing for resident logins
            ]);

            // 2. Prepare citizen data and link user_id if the column exists
            $citizenData = $request->except(['email', 'password']);
            if (\Schema::hasColumn('citizens', 'user_id')) {
                $citizenData['user_id'] = $user->id;
            }

            // 3. Create the Citizen Profile record
            $citizen = citizens::create($citizenData);

            $this->logActivity(
                'create',
                'citizen',
                $citizen->id,
                'Added new citizen and login account'
            );
        });

        return redirect()->route('citizenlist')->with('success', 'Citizen and login account registered successfully!');
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

        return view('bhw.healthrecord', compact('citizens', 'recentDiagnoses','totalRecords'));
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
        $citizen = citizens::with('healthRecords')->findOrFail($id);

        $this->logActivity(
            'view',
            'citizen',
            $citizen->id,
            'Viewed citizen details page'
        );

        return view('bhw.citizendetails', compact('citizen'));
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
}