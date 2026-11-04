<?php

use App\Support\FamilyCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->string('family_code')->nullable()->unique();
        });

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
            $sequence = ($sequences[$prefix] ?? 0) + 1;
            $sequences[$prefix] = $sequence;

            DB::table('families')
                ->where('id', $family->id)
                ->update([
                    'family_code' => 'F'.$prefix.'-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('families', function (Blueprint $table) {
            $table->dropUnique(['family_code']);
            $table->dropColumn('family_code');
        });
    }
};
