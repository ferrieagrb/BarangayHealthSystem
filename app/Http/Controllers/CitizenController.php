<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\citizens;
use App\Models\HealthRecord;
use App\Models\CitizenActivityLog;
use App\Models\HealthRecordActivityLog;
use Illuminate\Support\Facades\Auth;

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
    'citizen_id' => null, // correct
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

    // Citizen activity log
    $this->logActivity(
        'view',
        'citizen',
        $citizen->id,
        'Viewed citizen health record'
    );

    // ✅ Health record activity log (ADD THIS)
    HealthRecordActivityLog::create([
        'user_id' => Auth::id(),
        'action' => 'view',
        'citizen_id' => $citizen->id,   // 🔥 THIS is what you wanted
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
        'user_id' => Auth::id() ?? 0, // fallback prevents null crash
        'action' => $action,
        'module' => $module,
        'citizen_id' => $citizenId,
        'description' => $description,
    ]);
}

}