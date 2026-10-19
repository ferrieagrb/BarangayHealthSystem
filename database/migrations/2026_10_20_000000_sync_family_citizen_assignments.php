<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            'UPDATE citizens c
             INNER JOIN families f ON f.id = c.family_id
             INNER JOIN puroks p ON p.id = f.purok_id
             SET c.Citizen_Purok = p.name, c.subgroup_id = f.subgroup_id'
        );
    }

    public function down(): void
    {
        // Existing citizen values cannot be safely reconstructed after synchronization.
    }
};