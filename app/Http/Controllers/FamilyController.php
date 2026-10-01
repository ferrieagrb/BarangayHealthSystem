<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\citizens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FamilyController extends Controller
{
    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $families = Family::with(['purok', 'subgroup', 'members'])->latest()->get();
        $puroks = Purok::with('subgroups')->get();
        $availableCitizens = citizens::whereNull('family_id')->get();

        return view('bhw.families.show', compact('families', 'puroks', 'availableCitizens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($request->subgroup_id);

        Family::create([
            'family_name' => $request->family_name,
            'subgroup_id' => $request->subgroup_id,
            'purok_id' => $subgroup->purok_id,
        ]);

        return back()->with('success', 'Family created successfully.');
    }

    public function show(Family $family)
    {
        $family->load('members', 'purok', 'subgroup');
        $availableCitizens = citizens::whereNull('family_id')->get();
        $puroks = Purok::with('subgroups')->get();

        return view('bhw.families.show', compact('family', 'availableCitizens', 'puroks'));
    }

    public function addMember(Request $request, Family $family)
    {
        $request->validate(['citizen_id' => 'required|exists:citizens,id']);

        citizens::where('id', $request->citizen_id)->update([
            'family_id' => $family->id
        ]);

        return back()->with('success', 'Citizen added to family.');
    }

    public function removeMember($citizenId)
    {
        citizens::where('id', $citizenId)->update(['family_id' => null]);

        return back()->with('success', 'Citizen removed from family.');
    }

    public function update(Request $request, Family $family)
    {
        $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($request->subgroup_id);

        $family->update([
            'family_name' => $request->family_name,
            'subgroup_id' => $request->subgroup_id,
            'purok_id' => $subgroup->purok_id,
        ]);

        return back()->with('success', 'Family updated successfully.');
    }
}