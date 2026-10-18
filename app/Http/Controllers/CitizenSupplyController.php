<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use App\Models\SupplyRelease;
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

    $scheduledReleases = collect();
    if ($user = auth()->user()) {
        if ($user->citizen_id) {
            $scheduledReleases = SupplyRelease::with('supply')
                ->where('citizen_id', $user->citizen_id)
                ->latest('claim_date')
                ->get();
        }
    }

    return view('citizen.supplies', compact('availableSupplies', 'scheduledReleases', 'search'));
}
}