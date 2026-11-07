<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->boolean('public_health_id_enabled')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn('public_health_id_enabled');
        });
    }
};
