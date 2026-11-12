<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\citizens;
use App\Models\CitizenActivityLog;
use App\Services\BhwCitizenAccess;
use App\Services\FamilyCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FamilyController extends Controller
{
    private function authorizeFamilyWrite(): void
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->role === 'bhw' && $user->hasWriteAccess('family'),
            403,
            'You do not have permission to manage family records.'
        );
    }

    private function canMoveAcrossPuroks(): bool
    {
        $user = Auth::user();

        return $user && $user->role === 'bhw' && $user->hasWriteAccess('family_move_purok');
    }

    private function authorizeSubgroup(PurokSubgroup $subgroup, bool $allowOverride = false): void
    {
        $user = Auth::user();

        if ($user->role !== 'bhw' || ($allowOverride && $this->canMoveAcrossPuroks())) {
            return;
        }

        if (!app(BhwCitizenAccess::class)->assignedSubgroupIds($user)->contains($subgroup->id)) {
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
            $assignedSubgroupIds = app(BhwCitizenAccess::class)->assignedSubgroupIds($user);
            $familiesQuery->whereHas('subgroup', fn ($query) => $query->whereIn('id', $assignedSubgroupIds));
            $puroksQuery
                ->whereHas('subgroups', fn ($query) => $query->whereIn('id', $assignedSubgroupIds))
                ->with(['subgroups' => fn ($query) => $query->whereIn('id', $assignedSubgroupIds)]);
            if ($this->canMoveAcrossPuroks()) {
                $puroksQuery = Purok::with('subgroups');
            }
        }

        $subgroupCounts = (clone $familiesQuery)
            ->toBase()
            ->selectRaw('subgroup_id, count(*) as total')
            ->groupBy('subgroup_id')
            ->pluck('total', 'subgroup_id');

        // Keep overview counts independent of list filters, within the user's scope.
        $overview = (clone $familiesQuery)->withCount('members')->get();
        $familyOverview = [
            ['Total Families', $overview->count(), 'Registered households'],
            ['Family Members', $overview->sum('members_count'), 'Citizens assigned to households'],
            ['With Family Head', $overview->filter(fn ($family) => $family->members->contains('id', $family->head_id))->count(), 'Households with an assigned head'],
            ['New Families', $overview->where('created_at', '>=', now()->subDays(14))->count(), 'Registered in the last 14 days'],
        ];

        $request = request();
        $search = trim((string) $request->query('search', ''));
        $subgroupFilter = $request->query('subgroup_id');

        $familiesQuery->when($search !== '', function ($query) use ($search) {
            $query->whereHas('members', function ($members) use ($search) {
                // Match every name token against the same citizen, in any name order.
                foreach (preg_split('/\s+/u', $search, -1, PREG_SPLIT_NO_EMPTY) as $token) {
                    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $token) . '%';
                    $members->where(fn ($name) => $name->where('Citizen_FName', 'like', $like)
                        ->orWhere('Citizen_MName', 'like', $like)
                        ->orWhere('Citizen_LName', 'like', $like));
                }
            });
        });
        $familiesQuery->when($subgroupFilter, fn ($query) => $query->where('families.subgroup_id', $subgroupFilter));

        // Always order by purok number (Purok 1-4), then subgroup, then family name.
        $families = $familiesQuery
            ->leftJoin('puroks as order_puroks', 'order_puroks.id', '=', 'families.purok_id')
            ->leftJoin('purok_subgroups as order_subgroups', 'order_subgroups.id', '=', 'families.subgroup_id')
            ->select('families.*')
            ->orderByRaw('LENGTH(order_puroks.name)')
            ->orderBy('order_puroks.name')
            ->orderBy('order_subgroups.name')
            ->orderBy('families.family_name')
            ->paginate(10)
            ->withQueryString();
        $puroks = $puroksQuery->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->each(fn ($purok) => $purok->setRelation(
                'subgroups',
                $purok->subgroups->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()
            ));
        $availableCitizens = app(BhwCitizenAccess::class)
            ->scopeCitizens(citizens::query(), $user)
            ->whereNull('family_id')
            ->get();

        return view('bhw.families.show', compact('families', 'puroks', 'availableCitizens', 'subgroupCounts', 'search', 'subgroupFilter', 'familyOverview'));
    }

    public function store(Request $request)
    {
        $this->authorizeFamilyWrite();

        $validated = $request->validate([
            'subgroup_id' => 'required|exists:purok_subgroups,id',
            'citizen_ids' => 'required|array|min:1',
            'citizen_ids.*' => 'required|integer|distinct|exists:citizens,id',
        ]);

        $subgroup = PurokSubgroup::with('purok')->findOrFail($validated['subgroup_id']);
        $this->authorizeSubgroup($subgroup);

        $citizenIds = array_map('intval', $validated['citizen_ids']);
        $user = Auth::user();
        $access = app(BhwCitizenAccess::class);

        DB::transaction(function () use ($citizenIds, $subgroup, $user, $access): void {
            $selectedCitizens = $access->scopeCitizens(citizens::query(), $user)
                ->whereIn('id', $citizenIds)
                ->whereNull('family_id')
                ->where('subgroup_id', $subgroup->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($selectedCitizens->count() !== count($citizenIds)) {
                throw ValidationException::withMessages([
                    'citizen_ids' => 'One or more selected citizens are no longer available in this Purok / Subgroup.',
                ]);
            }

            $members = collect($citizenIds)->map(fn (int $id) => $selectedCitizens->get($id));
            $head = $members->first();
            $familyName = trim((string) $head->Citizen_LName) ?: trim((string) $head->Citizen_FName);
            $family = app(FamilyCodeGenerator::class)->create($familyName . ' Family', $subgroup);
            $family->update(['head_id' => $head->id]);

            foreach ($members as $citizen) {
                $citizen->update([
                    'family_id' => $family->id,
                    'Citizen_Purok' => $subgroup->purok->name,
                    'subgroup_id' => $subgroup->id,
                ]);
                $this->logAssignmentChange($citizen, 'Assigned citizen to family ' . $family->family_name);
            }
        });

        return back()->with('success', 'Family and selected household members created successfully.');
    }

    public function storeQuick(Request $request)
    {
        $this->authorizeFamilyWrite();

        $validated = $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($validated['subgroup_id']);
        $this->authorizeSubgroup($subgroup);
        $family = app(FamilyCodeGenerator::class)->create($validated['family_name'], $subgroup);

        return response()->json($family->load(['purok', 'subgroup']));
    }

    public function show(Family $family)
    {
        $this->authorizeFamily($family);
        $family->load('members', 'purok', 'subgroup');
        $user = Auth::user();
        $citizenAccess = app(BhwCitizenAccess::class);
        $availableCitizens = $citizenAccess
            ->scopeCitizens(citizens::query(), $user)
            ->whereNull('family_id')
            ->where(function ($query) use ($family) {
                $query->where('Citizen_Purok', $family->purok?->name)
                    ->orWhereHas('subgroup', fn ($subgroup) => $subgroup->where('purok_id', $family->purok_id));
            })
            ->get();
        $assignedSubgroupIds = $citizenAccess->assignedSubgroupIds($user);
        $puroks = $this->canMoveAcrossPuroks()
            ? Purok::with('subgroups')->get()
            : Purok::with(['subgroups' => fn ($query) => $query->whereIn('id', $assignedSubgroupIds)])
                ->whereHas('subgroups', fn ($query) => $query->whereIn('id', $assignedSubgroupIds))
                ->get();

        return view('bhw.families.show', compact('family', 'availableCitizens', 'puroks'));
    }

    public function addMember(Request $request, Family $family)
    {
        $this->authorizeFamilyWrite();

        $request->validate(['citizen_id' => 'required|exists:citizens,id']);

        $this->authorizeFamily($family);
        $this->syncFamilyPurok($family);

        $family->load(['purok', 'subgroup']);
        $citizen = citizens::findOrFail($request->citizen_id);
        $user = Auth::user();
        $access = app(BhwCitizenAccess::class);
        if ($user->role === 'bhw') {
            abort_unless(
                !$family->subgroup || (int) $family->subgroup->purok_id === (int) $family->purok_id,
                403,
                'The family subgroup must belong to its mother Purok.'
            );
            abort_unless(
                $access->canViewAll($user) || $access->canAccessCitizen($user, $citizen),
                403,
                'You are not assigned to this citizen record.'
            );

            // A purok mismatch is a user mistake, not a permission problem, so report it on the page.
            $citizenPurokId = $access->motherPurok($citizen)?->id;
            if ($citizenPurokId !== null && (int) $citizenPurokId !== (int) $family->purok_id) {
                return back()->with('error', 'This citizen belongs to a different purok than the family, so they cannot be added.');
            }
        }

        $previousFamilyId = $citizen->family_id;
        $previousSubgroupId = $citizen->subgroup_id;
        $citizen->update([
            'family_id' => $family->id,
            'Citizen_Purok' => $family->purok->name,
            'subgroup_id' => $family->subgroup?->id,
        ]);
        if ($previousFamilyId !== $citizen->family_id || $previousSubgroupId !== $citizen->subgroup_id) {
            $this->logAssignmentChange($citizen, 'Assigned citizen to family ' . $family->family_name);
        }

        return back()->with('success', 'Citizen added to family.');
    }

    public function removeMember($citizenId)
    {
        $this->authorizeFamilyWrite();

        $citizen = citizens::with('family.subgroup')->findOrFail($citizenId);
        $user = Auth::user();
        if ($user->role === 'bhw') {
            abort_unless(
                app(BhwCitizenAccess::class)->canAccessCitizen($user, $citizen),
                403,
                'You are not assigned to this citizen record.'
            );
        }

        if ($citizen->family) {
            $this->authorizeFamily($citizen->family);
        }

        if ($citizen->family && (int) $citizen->family->head_id === (int) $citizen->id) {
            $citizen->family->update(['head_id' => null]);
        }

        $citizen->update(['family_id' => null]);

        return back()->with('success', 'Citizen removed from family.');
    }

    public function setHead(Request $request, Family $family)
    {
        $this->authorizeFamilyWrite();
        $this->authorizeFamily($family);

        $validated = $request->validate(['citizen_id' => 'required|integer']);
        $member = $family->members()->find($validated['citizen_id']);

        if (! $member) {
            return back()->with('error', 'The selected citizen is not a member of this family.');
        }

        $family->update(['head_id' => $member->id]);

        return back()->with('success', trim($member->Citizen_FName.' '.$member->Citizen_LName).' is now the head of this family.');
    }

    public function archive(Family $family)
    {
        $this->authorizeFamilyWrite();
        $this->authorizeFamily($family);

        $name = $family->family_name;
        $family->delete();

        return redirect()->route('bhw.families.index')->with('success', "Family {$name} archived. You can restore it from the archive.");
    }

    // A family's purok must follow its subgroup. Older records can disagree, so repair them instead of rejecting the request.
    private function syncFamilyPurok(Family $family): void
    {
        $family->loadMissing('subgroup');

        if ($family->subgroup && (int) $family->subgroup->purok_id !== (int) $family->purok_id) {
            $family->update(['purok_id' => $family->subgroup->purok_id]);
            $family->load('purok');
        }
    }

    public function update(Request $request, Family $family)
    {
        $this->authorizeFamilyWrite();
        $this->authorizeFamily($family);
        $this->syncFamilyPurok($family);
        $request->validate([
            'family_name' => 'required|string|max:255',
            'subgroup_id' => 'required|exists:purok_subgroups,id',
        ]);

        $subgroup = PurokSubgroup::findOrFail($request->subgroup_id);
        $this->authorizeSubgroup($subgroup, true);
        if (Auth::user()->role === 'bhw' && !$this->canMoveAcrossPuroks() && (int) $subgroup->purok_id !== (int) $family->purok_id) {
            return back()->with('error', 'You cannot move a family to another mother Purok.');
        }

        $memberIds = $family->members()->pluck('id');
        $assignmentChanged = (int) $family->subgroup_id !== (int) $subgroup->id
            || (int) $family->purok_id !== (int) $subgroup->purok_id;
        $family->update([
            'family_name' => $request->family_name,
            'subgroup_id' => $request->subgroup_id,
            'purok_id' => $subgroup->purok_id,
        ]);

        $family->members()->update([
            'Citizen_Purok' => $subgroup->purok->name,
            'subgroup_id' => $subgroup->id,
        ]);

        if ($assignmentChanged) {
            foreach ($memberIds as $citizenId) {
                $citizen = citizens::find($citizenId);
                if ($citizen) {
                    $this->logAssignmentChange($citizen, 'Updated family subgroup assignment to ' . $subgroup->name);
                }
            }
        }

        return back()->with('success', 'Family updated successfully.');
    }

    private function logAssignmentChange(citizens $citizen, string $description): void
    {
        CitizenActivityLog::create([
            'user_id' => Auth::id() ?? 0,
            'action' => 'update',
            'module' => 'citizen',
            'citizen_id' => $citizen->id,
            'description' => $description,
        ]);
    }
}
