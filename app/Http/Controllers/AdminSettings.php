<?php

namespace App\Http\Controllers;

use App\Models\Purok;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurokSubgroup;
use App\Models\Supply;
use App\Models\UserPermission;
use App\Models\citizens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminSettings extends Controller
{
    public function index()
    {
        return view('admin.admin_settings');
    }

    public function demographicsIndex()
    {
        $puroks = Purok::withCount(['families', 'subgroups'])
            ->with('subgroups.bhws')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
        $puroks->each(function (Purok $purok) {
            $purok->setRelation('subgroups', $purok->subgroups->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values());
            $purok->setAttribute('purge_citizens_count', $this->citizensInPurokQuery($purok)->count());
        });
        $bhws = User::where('role', 'bhw')->orderBy('name')->get();
        $subgroups = PurokSubgroup::with(['purok', 'bhws', 'bhw'])
            ->withCount(['families', 'citizens'])
            ->get()
            ->sort(function ($a, $b) {
                return strnatcasecmp((string) optional($a->purok)->name, (string) optional($b->purok)->name)
                    ?: strnatcasecmp((string) $a->name, (string) $b->name);
            })
            ->values();
        $subgroups->each(function (PurokSubgroup $subgroup) {
            $subgroup->setAttribute('purge_citizens_count', $this->citizensInSubgroupQuery($subgroup)->count());
        });
        $demographicsMapData = [
            'puroks' => $puroks->map(fn ($purok) => [
                'id' => $purok->id,
                'name' => $purok->name,
                'latitude' => $purok->latitude,
                'longitude' => $purok->longitude,
                'boundary' => $purok->boundary,
            ])->values(),
            'subgroups' => $subgroups->map(fn ($subgroup) => [
                'id' => $subgroup->id,
                'purok_id' => $subgroup->purok_id,
                'name' => $subgroup->name,
                'latitude' => $subgroup->latitude,
                'longitude' => $subgroup->longitude,
                'boundary' => $subgroup->boundary,
            ])->values(),
            'routes' => [
                'purokPin' => route('admin.puroks.pin', ['purok' => '__ID__']),
                'purokBoundary' => route('admin.puroks.boundary', ['purok' => '__ID__']),
                'purokBoundaryDelete' => route('admin.puroks.boundary.destroy', ['purok' => '__ID__']),
                'subgroupPin' => route('admin.subgroups.pin', ['subgroup' => '__ID__']),
                'subgroupBoundary' => route('admin.subgroups.boundary', ['subgroup' => '__ID__']),
                'subgroupBoundaryDelete' => route('admin.subgroups.boundary.destroy', ['subgroup' => '__ID__']),
            ],
        ];

        return view('admin.demographics', compact('puroks', 'bhws', 'subgroups', 'demographicsMapData'));
    }

    public function storePurok(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:puroks,name',
            'description' => 'nullable|string|max:2000',
        ]);

        Purok::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Purok added successfully!');
    }

    public function updatePurok(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:puroks,name,' . $id,
            'description' => 'nullable|string|max:2000',
        ]);

        $purok = Purok::findOrFail($id);
        $updates = [
            'name' => $request->name,
            'description' => $request->description,
        ];

        if ($request->exists('latitude') || $request->exists('longitude')) {
            $coordinates = $this->validatedCoordinates($request);
            $updates = array_merge($updates, $coordinates);
        }

        DB::transaction(function () use ($purok, $updates) {
            $previousName = $purok->name;
            $purok->update($updates);

            if ($previousName !== $purok->name) {
                DB::table('citizens')
                    ->where('Citizen_Purok', $previousName)
                    ->update(['Citizen_Purok' => $purok->name]);
            }
        });

        return back()->with('success', 'Purok updated successfully!');
    }

    public function destroyPurok(Request $request, $id)
    {
        $purok = Purok::findOrFail($id);
        $hasCitizens = DB::table('citizens')
            ->where('Citizen_Purok', $purok->name)
            ->exists();

        if ($purok->families()->exists() || $purok->subgroups()->exists() || $hasCitizens) {
            return back()->withErrors([
                'purok' => 'This Purok cannot be archived while it has families, subgroups, or assigned citizens. Move those records first.',
            ]);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $purok->delete();

        return back()->with('success', 'Purok archived successfully!');
    }

    public function permanentlyDeletePurokCitizens(Request $request, $id)
    {
        $purok = Purok::findOrFail($id);
        $this->validatePurgeConfirmation($request, $purok->name);

        [$citizenCount, $accountCount] = DB::transaction(function () use ($purok) {
            $citizenIds = $this->citizensInPurokQuery($purok)->lockForUpdate()->pluck('id');

            if ($citizenIds->isEmpty()) {
                return [0, 0];
            }

            $accountCount = User::where('role', User::ROLE_CITIZEN)
                ->whereIn('citizen_id', $citizenIds)
                ->delete();
            $citizenCount = citizens::whereIn('id', $citizenIds)->delete();

            return [$citizenCount, $accountCount];
        });

        if ($citizenCount > 0) {
            Log::notice('Admin archived citizens in a Purok.', [
                'admin_id' => $request->user()->id,
                'purok_id' => $purok->id,
                'purok_name' => $purok->name,
                'citizen_count' => $citizenCount,
                'citizen_account_count' => $accountCount,
            ]);
        }

        $message = $citizenCount > 0
            ? "{$citizenCount} citizens and {$accountCount} linked citizen accounts were archived from {$purok->name}. They can be restored from the archive. Family records and BHW accounts were not changed."
            : "No citizens were found in {$purok->name}; nothing was archived.";

        return back()->with('success', $message)->with('demographics_tab', 'puroks');
    }

    public function storeSubgroup(Request $request)
    {
        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('purok_subgroups')->where('purok_id', $request->input('purok_id'))],
            'description' => 'nullable|string|max:2000',
            'bhw_ids' => 'sometimes|array',
            'bhw_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bhw_ids_present' => 'sometimes|in:1',
        ]);

        $bhwIds = $this->validatedBhwIds($request);

        DB::transaction(function () use ($request, $bhwIds) {
            $subgroup = PurokSubgroup::create([
                'purok_id' => $request->purok_id,
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $bhwIds[0] ?? null,
            ]);

            $subgroup->bhws()->sync($bhwIds);
        });

        return back()->with('success', 'Purok subgroup added successfully!');
    }

    public function updateSubgroup(Request $request, $id)
    {
        $request->validate([
            'purok_id' => 'required|exists:puroks,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('purok_subgroups')->where('purok_id', $request->input('purok_id'))->ignore($id)],
            'description' => 'nullable|string|max:2000',
            'bhw_ids' => 'sometimes|array',
            'bhw_ids.*' => ['integer', 'distinct', 'exists:users,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'bhw_ids_present' => 'sometimes|in:1',
        ]);

        $subgroup = PurokSubgroup::findOrFail($id);
        if (
            (int) $request->input('purok_id') !== (int) $subgroup->purok_id
            && ($subgroup->families()->exists() || $subgroup->citizens()->exists())
        ) {
            throw ValidationException::withMessages([
                'purok_id' => 'Move this subgroup’s families and citizens before changing its parent Purok.',
            ]);
        }
        $bhwIds = $this->validatedBhwIds($request, $subgroup);

        DB::transaction(function () use ($request, $subgroup, $bhwIds) {
            $subgroup->update([
                'purok_id' => $request->purok_id,
                'name' => $request->name,
                'description' => $request->description,
                'user_id' => $bhwIds[0] ?? null,
            ]);

            if ($request->exists('bhw_ids_present') || $request->exists('bhw_ids') || $request->exists('user_id')) {
                $subgroup->bhws()->sync($bhwIds);
            }
        });

        return back()->with('success', 'Purok subgroup updated successfully!');
    }

    public function destroySubgroup(Request $request, $id)
    {
        $subgroup = PurokSubgroup::findOrFail($id);

        if ($subgroup->families()->whereHas('members')->exists() || $subgroup->citizens()->exists()) {
            return back()->withErrors([
                'subgroup' => 'This subgroup cannot be archived while it has citizens. Move those records first.',
            ]);
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        DB::transaction(function () use ($subgroup) {
            $subgroup->families()->whereDoesntHave('members')->delete();
            $subgroup->delete();
        });

        return back()->with('success', 'Purok subgroup archived successfully!');
    }

    public function permanentlyDeleteSubgroupCitizens(Request $request, $id)
    {
        $subgroup = PurokSubgroup::findOrFail($id);
        $subgroup->loadMissing('purok');
        $confirmationName = "{$subgroup->name} - {$subgroup->purok->name}";
        $this->validatePurgeConfirmation($request, $confirmationName);

        [$citizenCount, $accountCount] = DB::transaction(function () use ($subgroup) {
            $citizenIds = $this->citizensInSubgroupQuery($subgroup)->lockForUpdate()->pluck('id');

            if ($citizenIds->isEmpty()) {
                return [0, 0];
            }

            $accountCount = User::where('role', User::ROLE_CITIZEN)
                ->whereIn('citizen_id', $citizenIds)
                ->delete();
            $citizenCount = citizens::whereIn('id', $citizenIds)->delete();

            return [$citizenCount, $accountCount];
        });

        if ($citizenCount > 0) {
            Log::notice('Admin archived citizens in a subgroup.', [
                'admin_id' => $request->user()->id,
                'purok_subgroup_id' => $subgroup->id,
                'subgroup_name' => $subgroup->name,
                'purok_name' => $subgroup->purok->name,
                'citizen_count' => $citizenCount,
                'citizen_account_count' => $accountCount,
            ]);
        }

        $message = $citizenCount > 0
            ? "{$citizenCount} citizens and {$accountCount} linked citizen accounts were archived from {$confirmationName}. They can be restored from the archive. Family records and BHW accounts were not changed."
            : "No citizens were found in {$confirmationName}; nothing was archived.";

        return back()->with('success', $message)->with('demographics_tab', 'subgroups');
    }

    public function updatePurokPin(Request $request, Purok $purok)
    {
        $coordinates = $this->validatedCoordinates($request);
        $purok->update($coordinates);

        return response()->json(['message' => 'Purok pin saved.', 'purok' => $purok->fresh()]);
    }

    public function updateSubgroupPin(Request $request, PurokSubgroup $subgroup)
    {
        $coordinates = $this->validatedCoordinates($request);
        $subgroup->update($coordinates);

        return response()->json(['message' => 'Subgroup pin saved.', 'subgroup' => $subgroup->fresh()]);
    }

    public function updatePurokBoundary(Request $request, Purok $purok)
    {
        $purok->update(['boundary' => $this->validatedBoundary($request)]);

        return response()->json(['message' => 'Purok boundary saved.']);
    }

    public function destroyPurokBoundary(Purok $purok)
    {
        $purok->update(['boundary' => null]);

        return response()->json(['message' => 'Purok boundary deleted.']);
    }

    public function updateSubgroupBoundary(Request $request, PurokSubgroup $subgroup)
    {
        $subgroup->update(['boundary' => $this->validatedBoundary($request)]);

        return response()->json(['message' => 'Subgroup boundary saved.']);
    }

    public function destroySubgroupBoundary(PurokSubgroup $subgroup)
    {
        $subgroup->update(['boundary' => null]);

        return response()->json(['message' => 'Subgroup boundary deleted.']);
    }

    private function validatedCoordinates(Request $request): array
    {
        $hasLatitude = $request->filled('latitude');
        $hasLongitude = $request->filled('longitude');

        if ($hasLatitude !== $hasLongitude) {
            throw ValidationException::withMessages([
                'latitude' => 'Latitude and longitude must both be provided, or both left empty to reset the pin.',
            ]);
        }

        $data = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        return [
            'latitude' => $hasLatitude ? $data['latitude'] : null,
            'longitude' => $hasLongitude ? $data['longitude'] : null,
        ];
    }

    private function validatedBoundary(Request $request): ?array
    {
        if (!$request->exists('boundary')) {
            throw ValidationException::withMessages([
                'boundary' => 'The boundary field is required. Send null only when intentionally clearing a saved boundary.',
            ]);
        }

        $boundary = $request->input('boundary');
        if ($boundary === null) {
            return null;
        }

        if (
            !is_array($boundary)
            || ($boundary['type'] ?? null) !== 'Polygon'
            || !isset($boundary['coordinates'][0])
            || !is_array($boundary['coordinates'][0])
        ) {
            throw ValidationException::withMessages([
                'boundary' => 'Boundary must be a GeoJSON polygon.',
            ]);
        }

        $ring = $boundary['coordinates'][0];
        if (count($ring) < 4) {
            throw ValidationException::withMessages([
                'boundary' => 'A boundary must contain at least three points and be closed.',
            ]);
        }

        foreach ($ring as $position) {
            if (
                !is_array($position)
                || count($position) !== 2
                || !is_numeric($position[0])
                || !is_numeric($position[1])
                || (float) $position[0] < -180
                || (float) $position[0] > 180
                || (float) $position[1] < -90
                || (float) $position[1] > 90
            ) {
                throw ValidationException::withMessages([
                    'boundary' => 'Boundary coordinates must be valid longitude/latitude pairs.',
                ]);
            }
        }

        $first = $ring[0];
        $last = $ring[count($ring) - 1];
        if ((float) $first[0] !== (float) $last[0] || (float) $first[1] !== (float) $last[1]) {
            throw ValidationException::withMessages([
                'boundary' => 'The boundary polygon must be closed by repeating its first point.',
            ]);
        }

        $area = 0.0;
        for ($index = 1; $index < count($ring); $index++) {
            $area += ((float) $ring[$index - 1][0] * (float) $ring[$index][1])
                - ((float) $ring[$index][0] * (float) $ring[$index - 1][1]);
        }
        if (abs($area) < 1.0e-10) {
            throw ValidationException::withMessages([
                'boundary' => 'Boundary must enclose an area and cannot be a straight line.',
            ]);
        }

        return $boundary;
    }

    private function validatePurgeConfirmation(Request $request, string $targetName): void
    {
        $request->validate([
            'confirmation' => 'required|string|max:513',
            'current_password' => ['required', 'current_password'],
        ]);

        if (!hash_equals($targetName, $request->string('confirmation')->toString())) {
            throw ValidationException::withMessages([
                'confirmation' => "Type {$targetName} exactly to confirm archiving.",
            ]);
        }
    }

    private function citizensInPurokQuery(Purok $purok)
    {
        return citizens::query()->where(function ($query) use ($purok) {
            $query->whereIn('family_id', $purok->families()->select('id'))
                ->orWhere(function ($query) use ($purok) {
                    $query->whereNull('family_id')
                        ->whereIn('subgroup_id', $purok->subgroups()->select('id'));
                })
                ->orWhere(function ($query) use ($purok) {
                    $query->whereNull('family_id')
                        ->whereNull('subgroup_id')
                        ->where('Citizen_Purok', $purok->name);
                });
        });
    }

    private function citizensInSubgroupQuery(PurokSubgroup $subgroup)
    {
        return citizens::query()->where(function ($query) use ($subgroup) {
            $query->whereIn('family_id', $subgroup->families()->select('id'))
                ->orWhere(function ($query) use ($subgroup) {
                    $query->whereNull('family_id')
                        ->where('subgroup_id', $subgroup->id);
                });
        });
    }

    private function validatedBhwIds(Request $request, ?PurokSubgroup $subgroup = null): array
    {
        if ($request->exists('bhw_ids_present') || $request->exists('bhw_ids')) {
            $ids = $request->input('bhw_ids', []);
        } elseif ($request->filled('user_id')) {
            $ids = [(int) $request->input('user_id')];
        } elseif ($subgroup) {
            $ids = $subgroup->bhws()->pluck('users.id')->all();
        } else {
            $ids = [];
        }

        $invalidIds = User::whereIn('id', $ids)
            ->where('role', '!=', 'bhw')
            ->pluck('id')
            ->all();

        if ($invalidIds) {
            throw ValidationException::withMessages([
                'bhw_ids' => 'Only users with the BHW role can be assigned to subgroups.',
            ]);
        }

        return array_values(array_unique(array_map('intval', $ids)));
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