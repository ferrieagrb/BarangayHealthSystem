<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;

class CitizenSupplyController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch search query if any
        $search = $request->input('search');

        // 2. Query supplies and group them by name to get total quantities
        $suppliesQuery = Supply::query();

        if ($search) {
            $suppliesQuery->where('name', 'like', "%{$search}%")
                          ->orWhere('category', 'like', "%{$search}%");
        }

        // Group by item name and calculate total available quantity
        $availableSupplies = $suppliesQuery
            ->select(
                'name',
                'category',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->groupBy('name', 'category')
            ->having('total_quantity', '>', 0)
            ->get();

        return view('citizen.supplies', compact('availableSupplies', 'search'));
    }
}