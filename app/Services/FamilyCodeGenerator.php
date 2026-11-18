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

            $sequence = $this->compact($codePrefix);

            return Family::create([
                'family_name' => $familyName,
                'subgroup_id' => $subgroup->id,
                'purok_id' => $purok->id,
                'family_code' => $codePrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            ]);
        });
    }

    public function archive(Family $family): void
    {
        DB::transaction(function () use ($family): void {
            Purok::withTrashed()->lockForUpdate()->findOrFail($family->purok_id);
            $family = Family::findOrFail($family->id);
            $codePrefix = $this->codePrefix($family);

            $family->delete();
            $this->compact($codePrefix);
        });
    }

    public function restore(Family $family): void
    {
        DB::transaction(function () use ($family): void {
            Purok::withTrashed()->lockForUpdate()->findOrFail($family->purok_id);
            $family = Family::onlyTrashed()->findOrFail($family->id);
            $codePrefix = $this->codePrefix($family);
            $sequence = $this->compact($codePrefix);

            // Restored households join the end of the current area's sequence.
            $family->family_code = $codePrefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            if ($family->head_id && ! $family->members()->whereKey($family->head_id)->exists()) {
                $family->head_id = null;
            }
            $family->restore();
        });
    }

    public function renumberExisting(): void
    {
        DB::transaction(function (): void {
            Purok::withTrashed()->orderBy('id')->lockForUpdate()->get();

            $prefixes = Family::withTrashed()->whereNotNull('family_code')
                ->pluck('family_code')
                ->map(fn (string $code): string => preg_replace('/\d+$/', '', $code))
                ->unique();

            foreach ($prefixes as $codePrefix) {
                $this->compact($codePrefix);
            }
        });
    }

    private function codePrefix(Family $family): string
    {
        if (preg_match('/^(F.+-)(\d+)$/', $family->family_code ?? '', $matches)) {
            return $matches[1];
        }

        return 'F'.FamilyCode::prefixFor($family->purok->name, $family->subgroup?->name).'-';
    }

    // Caller holds the Purok lock to serialize changes to this area's sequence.
    private function compact(string $codePrefix): int
    {
        $families = Family::withTrashed()
            ->where('family_code', 'like', $codePrefix.'%')
            ->orderBy('id')
            ->get()
            ->filter(fn (Family $family): bool => (bool) preg_match(
                '/^'.preg_quote($codePrefix, '/').'\d+$/', $family->family_code
            ));

        $active = $families->reject(fn (Family $family): bool => $family->trashed())
            ->sortBy(fn (Family $family): int => (int) substr($family->family_code, strlen($codePrefix)))
            ->values();
        $changes = [];
        foreach ($active as $index => $family) {
            $code = $codePrefix.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            if ($family->family_code !== $code) {
                $changes[$family->id] = $code;
            }
        }

        // Release old codes before filling gaps to respect the unique index.
        $releaseIds = $families->filter(fn (Family $family): bool => $family->trashed())
            ->pluck('id')->merge(array_keys($changes));
        if ($releaseIds->isNotEmpty()) {
            DB::table('families')->whereIn('id', $releaseIds)->update(['family_code' => null]);
        }
        foreach ($changes as $id => $code) {
            DB::table('families')->where('id', $id)->update(['family_code' => $code]);
        }

        return $active->count() + 1;
    }
}
