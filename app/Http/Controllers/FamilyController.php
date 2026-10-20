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
    private function authorizeSubgroup(PurokSubgroup $subgroup): void
    {
        $user = Auth::user();

        if ($user->role !== 'bhw') {
            return;
        }

        if ((int) $subgroup->user_id !== (int) $user->id) {
            abort(403, 'You can only manage families assigned to your subgroup.');
        }
    }

    private function authorizeFamily(Family $family): void
    {
        $subgroup = $family->loadMissing('subgroup')->subgroup;

        if (!$subgroup) {
            abort(403, 'This family is not assigned to a subgroup.');
        }

        $this->authorizeSubgroup($subgroup);
    }

    public function index()
    {
        if (!Auth::check() || Auth::user()->role !== 'bhw') {
            abort(403);
        }

        $user = Auth::user();
        $familiesQuery = Family::with(['purok', 'subgroup', 'members']);
        $puroksQuery = Purok::with('subgroups');

        if ($user->role === 'bhw') {
            $familiesQuery->whereHas('subgroup', fn ($query) => $query->where('user_id', $user->id));
            $puroksQuery
                ->whereHas('subgroups', fn ($query) => $query->where('user_id', $user->id))
                ->with(['subgroups' => fn ($query) => $query->where('user_id', $user->id)]);
        }

        $families = $familiesQuery->latest()->get();
        $puroks = $puroksQuery->get();
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
        $this->authorizeSubgroup($subgroup);

        Family::create([
            'family_name' => $request->family_name,
            'subgroup_id' => $request->subgroup_id,
            'purok_id' => $subgroup->purok_id,
        ]);

        return back()->with('success', 'Family created successfully.');
    }

    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($validated['subgroup_id']);
        $this->authorizeSubgroup($subgroup);
        $family = Family::create([
            'family_name' => $validated['family_name'],
            'subgroup_id' => $subgroup->id,
            'purok_id' => $subgroup->purok_id,
        ]);

        return response()->json($family->load(['purok', 'subgroup']));
    }

    public function show(Family $family)
    {
        $this->authorizeFamily($family);
        $family->load('members', 'purok', 'subgroup');
        $availableCitizens = citizens::whereNull('family_id')->get();
        $puroks = Purok::with('subgroups')->get();

        return view('bhw.families.show', compact('family', 'availableCitizens', 'puroks'));
    }

    public function addMember(Request $request, Family $family)
    {
        $request->validate(['citizen_id' => 'required|exists:citizens,id']);

        $this->authorizeFamily($family);

        $family->load(['purok', 'subgroup']);
        citizens::where('id', $request->citizen_id)->update([
            'family_id' => $family->id,
            'Citizen_Purok' => $family->purok->name,
            'subgroup_id' => $family->subgroup->id,
        ]);

        return back()->with('success', 'Citizen added to family.');
    }

    public function removeMember($citizenId)
    {
        $citizen = citizens::with('family.subgroup')->findOrFail($citizenId);

        if ($citizen->family) {
            $this->authorizeFamily($citizen->family);
        }

        $citizen->update(['family_id' => null]);

        return back()->with('success', 'Citizen removed from family.');
    }

    public function update(Request $request, Family $family)
    {
        $this->authorizeFamily($family);
        $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($request->subgroup_id);
        $this->authorizeSubgroup($subgroup);

        $family->update([
            'family_name' => $request->family_name,
            'subgroup_id' => $request->subgroup_id,
            'purok_id' => $subgroup->purok_id,
        ]);

        $family->members()->update([
            'Citizen_Purok' => $subgroup->purok->name,
            'subgroup_id' => $subgroup->id,
        ]);

        return back()->with('success', 'Family updated successfully.');
    }
}