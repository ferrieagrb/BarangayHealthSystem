<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('citizens') && !Schema::hasColumn('citizens', 'Citizen_MName')) {
            Schema::table('citizens', function (Blueprint $table) {
                $table->string('Citizen_MName', 255)->nullable()->after('Citizen_FName');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('citizens') && Schema::hasColumn('citizens', 'Citizen_MName')) {
            Schema::table('citizens', function (Blueprint $table) {
                $table->dropColumn('Citizen_MName');
            });
        }
    }
};
