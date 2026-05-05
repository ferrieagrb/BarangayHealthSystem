<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supply;
use App\Models\Log;
use App\Models\SupplyLog;
use Illuminate\Support\Facades\Auth;
use App\Models\citizens;

class SupplyController extends Controller

{

    public function index(Request $request)
{
    $query = Supply::query();

    if ($request->status == 'low_stock') {
        $query->whereColumn('quantity', '<=', 'min_stock')
              ->where('quantity', '>', 0);
    }

    if ($request->status == 'out_of_stock') {
        $query->where('quantity', '<=', 0);
    }

    if ($request->status == 'in_stock') {
        $query->whereColumn('quantity', '>', 'min_stock');
    }

    $supplies = $query->get();

    $totalsupply = Supply::count();

    $wellStocked = Supply::whereColumn('quantity', '>', 'min_stock')->count();

    $lowStock = Supply::whereColumn('quantity', '<=', 'min_stock')
        ->where('quantity', '>', 0)
        ->count();

    $citizens = citizens::all();

    return view('bhw.supplies', compact(
        'supplies',
        'totalsupply',
        'wellStocked',
        'lowStock',
        'citizens'
    ));
}

    public function deposit(Request $request)
{
    $request->validate([
        'id' => 'required',
        'quantity' => 'required|integer|min:1'
    ]);

    $supply = Supply::findOrFail($request->id);

    $supply->quantity += $request->quantity;
    $supply->save();

    SupplyLog::create([
        'action' => 'deposit',
        'supply_id' => $supply->id,
        'quantity' => $request->quantity,
        'user_id' => Auth::id(),
        'citizen_id' => null,
        'notes' => $request->notes ?? null,
    ]);

    return back()->with('success', 'Stock deposited successfully');
}

public function release(Request $request)
{
    $request->validate([
        'id' => 'required',
        'quantity' => 'required|integer|min:1',
        'citizen_id' => 'required'
    ]);

    $supply = Supply::findOrFail($request->id);

    $supply->quantity -= $request->quantity;

    if ($supply->quantity < 0) {
        $supply->quantity = 0;
    }

    $supply->save();

    SupplyLog::create([
        'action' => 'release',
        'supply_id' => $supply->id,
        'quantity' => $request->quantity,
        'user_id' => Auth::id(),
        'citizen_id' => $request->citizen_id,
        'notes' => $request->notes ?? null,
    ]);

    return back();
}
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        Supply::create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'category' => $request->category,
            'min_stock' => $request->min_stock,
        ]);

        return redirect()->route('supplies.create')->with('success', 'Supply created successfully');
    }
    
}
