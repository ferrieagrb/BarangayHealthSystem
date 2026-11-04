<?php

namespace App\Services;

use App\Models\PurokSubgroup;
use App\Models\Purok;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\citizens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BhwCitizenAccess
{
    public function canViewAll(User $user): bool
    {
        return UserPermission::query()
            ->where('user_id', $user->id)
            ->where('feature', 'view_all_citizens')
            ->where('access', '!=', 'none')
            ->exists();
    }

    public function assignedSubgroupIds(User $user): Collection
    {
        return $user->assignedSubgroups()
            ->pluck('purok_subgroups.id')
            ->merge(PurokSubgroup::query()->where('user_id', $user->id)->pluck('id'))
            ->unique()
            ->values();
    }

    public function assignedPurokIds(User $user): Collection
    {
        $subgroupPurokIds = PurokSubgroup::query()
            ->whereIn('id', $this->assignedSubgroupIds($user))
            ->pluck('purok_id');

        $directPurokIds = Schema::hasColumn('puroks', 'user_id')
            ? Purok::query()->where('user_id', $user->id)->pluck('id')
            : collect();
        $pivotPurokIds = Schema::hasTable('purok_user')
            ? $user->assignedPuroks()->pluck('puroks.id')
            : collect();

        return $subgroupPurokIds
            ->merge($directPurokIds)
            ->merge($pivotPurokIds)
            ->unique()
            ->values();
    }

    public function motherPurok(citizens $citizen): ?Purok
    {
        $citizen->loadMissing(['subgroup.purok', 'family.purok']);

        if ($citizen->subgroup?->purok) {
            return $citizen->subgroup->purok;
        }

        if ($citizen->family?->purok) {
            return $citizen->family->purok;
        }

        $purokValue = trim((string) $citizen->Citizen_Purok);
        if ($purokValue === '') {
            return null;
        }

        return Purok::withTrashed()
            ->where('name', $purokValue)
            ->when(
                ctype_digit($purokValue),
                fn (Builder $query) => $query->orWhere('id', (int) $purokValue)
            )
            ->first();
    }

    public function scopeCitizens(Builder $query, User $user): Builder
    {
        if ($this->canViewAll($user)) {
            return $query;
        }

        $subgroupIds = $this->assignedSubgroupIds($user);
        $purokIds = $this->assignedPurokIds($user);

        if ($subgroupIds->isEmpty() && $purokIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $purokValues = Purok::withTrashed()
            ->whereIn('id', $purokIds)
            ->get(['id', 'name'])
            ->flatMap(fn (Purok $purok) => [$purok->name, (string) $purok->id])
            ->unique()
            ->values();

        return $query->where(function (Builder $citizensQuery) use ($subgroupIds, $purokIds, $purokValues) {
            if ($subgroupIds->isNotEmpty()) {
                $citizensQuery->whereIn('subgroup_id', $subgroupIds)
                    ->orWhereHas('family', fn (Builder $familyQuery) => $familyQuery->whereIn('subgroup_id', $subgroupIds));
            }

            if ($purokIds->isNotEmpty()) {
                $citizensQuery->orWhere(function (Builder $unassignedQuery) use ($purokIds, $purokValues) {
                    $unassignedQuery
                        ->whereNull('subgroup_id')
                        ->whereDoesntHave('family', fn (Builder $familyQuery) => $familyQuery->whereNotNull('subgroup_id'))
                        ->where(function (Builder $locationQuery) use ($purokIds, $purokValues) {
                            $locationQuery
                                ->whereHas('family', fn (Builder $familyQuery) => $familyQuery->whereIn('purok_id', $purokIds))
                                ->orWhere(function (Builder $withoutFamilyQuery) use ($purokValues) {
                                    $withoutFamilyQuery
                                        ->whereDoesntHave('family')
                                        ->whereIn('Citizen_Purok', $purokValues);
                                });
                        });
                });
            }
        });
    }

    public function canAccessCitizen(User $user, citizens $citizen): bool
    {
        $subgroupIds = $this->assignedSubgroupIds($user);
        $citizen->loadMissing('family');

        if ($citizen->subgroup_id !== null || $citizen->family?->subgroup_id !== null) {
            return $subgroupIds->contains($citizen->subgroup_id)
                || $subgroupIds->contains($citizen->family?->subgroup_id);
        }

        $motherPurokId = $this->motherPurok($citizen)?->id;

        return $motherPurokId !== null
            && $this->assignedPurokIds($user)->contains($motherPurokId);
    }
}
