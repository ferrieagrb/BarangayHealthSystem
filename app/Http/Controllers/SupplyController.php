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

        $totalsupply = Supply::sum('quantity');

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
            'name' => 'required|string',
            'batches' => 'required|array|min:1',
            'batches.*.item_number' => 'nullable|string|max:255',
            'batches.*.serial_number' => 'nullable|string|max:255',
            'batches.*.quantity' => 'required|integer|min:1',
            'batches.*.unit' => 'nullable|string|max:50',
            'batches.*.expiration_date' => 'required|date',
            'batches.*.supplier' => 'nullable|string|max:255',
        ]);

        $itemName = $request->input('name');
        
        // Pull shared catalog properties (like category and min_stock) from an existing record if available
        $template = Supply::where('name', $itemName)->first();

        foreach ($request->input('batches') as $batchData) {
            $supply = Supply::create([
                'name' => $itemName,
                'category' => $template->category ?? 'Supplies',
                'min_stock' => $template->min_stock ?? 5,
                'item_number' => $batchData['item_number'] ?? null,
                'serial_number' => $batchData['serial_number'] ?? null,
                'quantity' => $batchData['quantity'],
                'unit' => $batchData['unit'] ?? null,
                'expiration_date' => $batchData['expiration_date'],
                'supplier' => $batchData['supplier'] ?? null,
                'status' => 'Available'
            ]);

            SupplyLog::create([
                'action' => 'deposit',
                'supply_id' => $supply->id,
                'quantity' => $batchData['quantity'],
                'user_id' => Auth::id(),
                'citizen_id' => null,
                'notes' => 'New batch deposited',
            ]);
        }

        return back()->with('success', 'New batches deposited successfully.');
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
        'min_stock' => 'required|integer|min:0',
        'description' => 'nullable|string',
    ]);

    // Ensure we don't duplicate or create phantom stock rows
    Supply::create([
        'name' => $request->name,
        'category' => $request->category,
        'quantity' => 0, // Starts at zero until a batch is deposited
        'min_stock' => $request->min_stock,
        'status' => 'Out of Stock'
    ]);

    return redirect()->route('supplies.index')->with('success', 'Item catalog created successfully.');
}
}