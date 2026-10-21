<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance_records')
            && !Schema::hasColumn('maintenance_records', 'quantity_dispensed')) {
            Schema::table('maintenance_records', function (Blueprint $table) {
                $table->unsignedInteger('quantity_dispensed')->default(1)->after('medicine_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_records')
            && Schema::hasColumn('maintenance_records', 'quantity_dispensed')) {
            Schema::table('maintenance_records', function (Blueprint $table) {
                $table->dropColumn('quantity_dispensed');
            });
        }
    }
};
