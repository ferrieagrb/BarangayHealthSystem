<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccination_records', function (Blueprint $table) {
            if (!Schema::hasColumn('vaccination_records', 'cvx_code')) {
                $table->string('cvx_code', 20)->nullable();
            }
            if (!Schema::hasColumn('vaccination_records', 'vaccine_formulation')) {
                $table->text('vaccine_formulation')->nullable();
            }
            if (!Schema::hasColumn('vaccination_records', 'historical_recording')) {
                $table->boolean('historical_recording')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('vaccination_records', function (Blueprint $table) {
            $columns = array_values(array_filter(
                ['cvx_code', 'vaccine_formulation', 'historical_recording'],
                fn (string $column): bool => Schema::hasColumn('vaccination_records', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
