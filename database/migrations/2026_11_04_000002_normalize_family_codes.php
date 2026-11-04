<?php

use App\Support\FamilyCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existingSequences = DB::table('families')
            ->pluck('family_code', 'id')
            ->mapWithKeys(fn (?string $code, int $id): array => [
                $id => is_string($code) && preg_match('/-(\d+)$/', $code, $matches)
                    ? (int) $matches[1]
                    : null,
            ])
            ->all();

        DB::table('families')->update(['family_code' => null]);

        $sequences = [];
        $families = DB::table('families')
            ->leftJoin('puroks', 'families.purok_id', '=', 'puroks.id')
            ->leftJoin('purok_subgroups', 'families.subgroup_id', '=', 'purok_subgroups.id')
            ->orderBy('families.id')
            ->select([
                'families.id',
                'puroks.name as purok_name',
                'purok_subgroups.name as subgroup_name',
            ])
            ->cursor();

        foreach ($families as $family) {
            $prefix = FamilyCode::prefixFor($family->purok_name ?? '', $family->subgroup_name);
            $sequence = $existingSequences[$family->id] ?? (($sequences[$prefix] ?? 0) + 1);
            while (isset($sequences[$prefix][$sequence])) {
                $sequence++;
            }
            $sequences[$prefix][$sequence] = true;

            DB::table('families')
                ->where('id', $family->id)
                ->update([
                    'family_code' => 'F'.$prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                ]);
        }
    }

    public function down(): void
    {
        // Family codes are identifiers, so reverting would risk restoring invalid or duplicate values.
    }
};
