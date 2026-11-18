<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->date('Citizen_BirthDate')->nullable()->change();
            $table->integer('Citizen_Age')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep citizens with unknown birth dates and ages intact on rollback.
    }
};
