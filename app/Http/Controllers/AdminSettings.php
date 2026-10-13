<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurokSubgroup;
use App\Models\Supply;
use App\Models\UserPermission;

class AdminSettings extends Controller
{
    public function index()
    {
        return view('admin.admin_settings');
    }

    public function demographicsIndex()
    {
        // Fetch puroks (no longer bound directly to a single BHW)
        $puroks = Purok::all();
        
        // Fetch users who have the 'bhw' role to populate the dropdown
        $bhws = User::where('role', 'bhw')->get();

        // Fetch subgroups along with their parent purok and assigned BHW
        $subgroups = PurokSubgroup::with(['purok', 'bhw'])->get();

        return view('admin.demographics', compact('puroks', 'bhws', 'subgroups'));
    }

    public function storePurok(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:puroks,name',
        ]);

        Purok::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Purok added successfully!');
    }

    public function updatePurok(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:puroks,name,' . $id,
        ]);

        $purok = Purok::findOrFail($id);
        
        $purok->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Purok updated successfully!');
    }

    public function destroyPurok($id)
    {
        Purok::findOrFail($id)->delete();

        return back()->with('success', 'Purok deleted successfully!');
    }

    public function storeSubgroup(Request $request)
    {
        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id', // 👈 BHW assigned to this subgroup
        ]);

        PurokSubgroup::create([
            'purok_id' => $request->purok_id,
            'name' => $request->name,
            'user_id' => $request->user_id,
        ]);

        return back()->with('success', 'Purok subgroup added successfully!');
    }

    public function updateSubgroup(Request $request, $id)
    {
        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id', // 👈 Validate user_id
        ]);

        $subgroup = PurokSubgroup::findOrFail($id);
        
        $subgroup->update([
            'purok_id' => $request->purok_id,
            'name' => $request->name,
            'user_id' => $request->user_id, // 👈 Save user_id to the database table
        ]);

        return back()->with('success', 'Purok subgroup updated successfully!');
    }

    public function destroySubgroup($id)
    {
        PurokSubgroup::findOrFail($id)->delete();
        return back()->with('success', 'Purok subgroup deleted successfully!');
    }

    public function dashboard()
    {
        $bhwCount = User::where('role', 'bhw')->count();
        $lowStockCount = Supply::where('quantity', '<=', 5)->count();

        // Chart 2 Data: Supply Category Distribution
        $categories = Supply::select('category')
            ->selectRaw('SUM(quantity) as total_qty')
            ->groupBy('category')
            ->pluck('total_qty', 'category');

        $categoryLabels = $categories->keys()->toArray();
        $categoryValues = $categories->values()->toArray();

        // Chart 3 Data: BHW Permission Access Breakdown
        $writeCount = UserPermission::where('access', 'write')->count();
        $readCount = UserPermission::where('access', 'read')->count();
        $noneCount = UserPermission::where('access', 'none')->count();

        return view('admin.admin_home', compact(
            'bhwCount',
            'lowStockCount',
            'categoryLabels',
            'categoryValues',
            'writeCount',
            'readCount',
            'noneCount'
        ));
    }
}