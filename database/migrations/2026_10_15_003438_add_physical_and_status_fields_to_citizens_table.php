
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            // Physical Information
            $table->decimal('Citizen_Height', 5, 2)->nullable()
                ->comment('Height in centimeters');

            $table->decimal('Citizen_Weight', 5, 2)->nullable()
                ->comment('Weight in kilograms');

            // Sex
            $table->enum('Citizen_Sex', [
                'Male',
                'Female',
            ])->nullable();

            // Housing Status
            $table->enum('Citizen_HousingStatus', [
                'Permanent Resident',
                'Renter',
            ])->nullable();

            // Health and Family Planning
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);

            // Vital Status
            $table->boolean('Citizen_IsDeceased')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('citizens', function (Blueprint $table) {
            $table->dropColumn([
                'Citizen_Height',
                'Citizen_Weight',
                'Citizen_Sex',
                'Citizen_HousingStatus',
                'Citizen_Pregnant',
                'Citizen_InPills',
                'Citizen_IsDeceased',
            ]);
        });
    }
};
