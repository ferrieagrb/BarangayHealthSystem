<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use App\Models\SupplyRequest;
use App\Models\citizens;
use Illuminate\Support\Facades\DB;

class CitizenSupplyController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');

    $suppliesQuery = Supply::query();

    if ($search) {
        $suppliesQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
    }

    $availableSupplies = $suppliesQuery
        ->select(
            'id',
            'name',
            'category',
            DB::raw('SUM(quantity) as total_quantity')
        )
        ->groupBy('id', 'name', 'category')
        ->having('total_quantity', '>', 0)
        ->get();

    // Fetch the logged-in citizen's requests securely via citizen_id link
    $user = auth()->user();
    
    $myRequests = collect();
    
    // Check if the user record has the citizen_id foreign key set
    if ($user->citizen_id) {
        $myRequests = SupplyRequest::with('supply')
            ->where('citizen_id', $user->citizen_id)
            ->latest()
            ->get();
    }

    return view('citizen.supplies', compact('availableSupplies', 'myRequests', 'search'));
}

    /**
     * Handle the citizen's request for a specific available supply.
     */
    public function storeReservation(Request $request)
{
    $request->validate([
        'citizen_id' => 'required|exists:citizens,id',
        'supply_id' => 'required|exists:supplies,id',
        'quantity' => 'required|integer|min:1',
        'priority' => 'required|in:normal,high,urgent',
        'bhw_remarks' => 'nullable|string|max:500',
    ]);

    $supply = Supply::findOrFail($request->supply_id);

    if ($supply->quantity < $request->quantity) {
        return back()->with('error', 'Insufficient stock available to reserve this quantity.');
    }

    $supply->decrement('quantity', $request->quantity);

    SupplyRequest::create([
        'citizen_id' => $request->citizen_id,
        'supply_id' => $request->supply_id,
        'quantity' => $request->quantity,
        'priority' => $request->priority,
        'status' => 'approved',
        'bhw_remarks' => $request->bhw_remarks ? $request->bhw_remarks . ' (Directly assigned by BHW)' : 'Directly assigned and reserved by BHW.',
    ]);

    return back()->with('success', 'Supply successfully reserved and assigned to the citizen. Stock has been deducted.');
}

public function storeRequest(Request $request)
{
    $request->validate([
        'supply_id' => 'required|exists:supplies,id',
        'quantity' => 'required|integer|min:1',
        'remarks' => 'nullable|string|max:500',
    ]);

    $user = auth()->user();

    if (!$user->citizen_id) {
        return back()->with('error', 'Your account is not linked to a citizen profile. Please contact a BHW.');
    }

    \App\Models\SupplyRequest::create([
        'citizen_id' => $user->citizen_id,
        'supply_id' => $request->supply_id,
        'quantity' => $request->quantity,
        'remarks' => $request->remarks,
        'priority' => 'normal',
        'status' => 'pending',
    ]);

    return back()->with('success', 'Supply request submitted successfully!');
}
}