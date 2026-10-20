<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn(['employment_status', 'is_out_of_school_youth', 'contraceptive_method']);
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->string('employment_status')->nullable()->after('Citizen_HousingStatus');
            $table->boolean('is_out_of_school_youth')->default(false)->after('employment_status');
            $table->string('contraceptive_method')->nullable()->after('Citizen_InPills');
        });
    }
};
