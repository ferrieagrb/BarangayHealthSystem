<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('citizens')
            ->orderBy('id')
            ->chunkById(500, function ($citizens): void {
                $clearFamilyPlanning = [];
                $clearPills = [];

                foreach ($citizens as $citizen) {
                    if ($citizen->Citizen_Sex !== 'Female') {
                        if ((bool) $citizen->Citizen_Pregnant || (bool) $citizen->Citizen_InPills) {
                            $clearFamilyPlanning[] = $citizen->id;
                        }

                        continue;
                    }

                    $age = $citizen->Citizen_BirthDate
                        ? Carbon::parse($citizen->Citizen_BirthDate)->age
                        : (is_numeric($citizen->Citizen_Age) ? (int) $citizen->Citizen_Age : null);

                    if (($age === null || $age < 16) && (bool) $citizen->Citizen_InPills) {
                        $clearPills[] = $citizen->id;
                    }
                }

                if ($clearFamilyPlanning) {
                    DB::table('citizens')
                        ->whereIn('id', $clearFamilyPlanning)
                        ->update([
                            'Citizen_Pregnant' => false,
                            'Citizen_InPills' => false,
                        ]);
                }

                if ($clearPills) {
                    DB::table('citizens')
                        ->whereIn('id', $clearPills)
                        ->update(['Citizen_InPills' => false]);
                }
            });
    }

    public function down(): void
    {
        // Eligibility cleanup is intentionally irreversible.
    }
};
