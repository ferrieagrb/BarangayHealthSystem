<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->string('severity', 30)->nullable()->after('comments');
            $table->decimal('weight', 6, 2)->nullable()->after('severity');
            $table->decimal('height', 6, 2)->nullable()->after('weight');
            $table->decimal('bmi', 5, 1)->nullable()->after('height');
            $table->decimal('temperature', 4, 1)->nullable()->after('bmi');
            $table->string('blood_pressure', 20)->nullable()->after('temperature');
        });
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn(['severity', 'weight', 'height', 'bmi', 'temperature', 'blood_pressure']);
        });
    }
};
