<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Support\FamilyCode;
use Illuminate\Support\Facades\DB;

class FamilyCodeGenerator
{
    public function create(string $familyName, PurokSubgroup $subgroup): Family
    {
        return DB::transaction(function () use ($familyName, $subgroup): Family {
            $subgroup = PurokSubgroup::query()
                ->withTrashed()
                ->findOrFail($subgroup->id);
            $purok = Purok::query()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($subgroup->purok_id);
            $prefix = FamilyCode::prefixFor($purok->name, $subgroup->name);
            $codePrefix = "F{$prefix}-";

            $sequence = Family::withTrashed()
                ->where('family_code', 'like', $codePrefix.'%')
                ->pluck('family_code')
                ->map(fn (string $code): int => preg_match('/^'.preg_quote($codePrefix, '/').'(\d+)$/', $code, $matches)
                    ? (int) $matches[1]
                    : 0)
                ->max() + 1;

            return Family::create([
                'family_name' => $familyName,
                'subgroup_id' => $subgroup->id,
                'purok_id' => $purok->id,
                'family_code' => $codePrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            ]);
        });
    }
}
