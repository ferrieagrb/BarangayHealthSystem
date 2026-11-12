<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->string('Citizen_ContactNo')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Keep existing citizens without contact numbers intact on rollback.
    }
};
