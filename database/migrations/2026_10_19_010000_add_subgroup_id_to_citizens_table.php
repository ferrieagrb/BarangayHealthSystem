<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->foreignId('subgroup_id')->nullable()->after('Citizen_Purok')->constrained('purok_subgroups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropForeign(['subgroup_id']);
            $table->dropColumn('subgroup_id');
        });
    }
};