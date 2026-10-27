<?php

namespace App\Services;

use App\Models\PurokSubgroup;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\citizens;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    public function scopeCitizens(Builder $query, User $user): Builder
    {
        if ($this->canViewAll($user)) {
            return $query;
        }

        $subgroupIds = $this->assignedSubgroupIds($user);
        if ($subgroupIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $citizensQuery) use ($subgroupIds) {
            $citizensQuery->whereIn('subgroup_id', $subgroupIds)
                ->orWhereHas('family', fn (Builder $familyQuery) => $familyQuery->whereIn('subgroup_id', $subgroupIds));
        });
    }

    public function canAccessCitizen(User $user, citizens $citizen): bool
    {
        $subgroupIds = $this->assignedSubgroupIds($user);
        $citizen->loadMissing('family');

        return $subgroupIds->contains($citizen->subgroup_id)
            || $subgroupIds->contains($citizen->family?->subgroup_id);
    }
}
