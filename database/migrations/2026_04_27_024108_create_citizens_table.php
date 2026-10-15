
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citizens', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('Citizen_FName');
            $table->string('Citizen_LName');
            $table->date('Citizen_BirthDate');
            $table->string('Citizen_ContactNo');
            $table->integer('Citizen_Age');

            // Physical Information
            $table->decimal('Citizen_Height', 5, 2)->nullable()
                ->comment('Height in centimeters');

            $table->decimal('Citizen_Weight', 5, 2)->nullable()
                ->comment('Weight in kilograms');

            // Sex
            $table->enum('Citizen_Sex', [
                'Male',
                'Female'
            ])->nullable();

            // Housing Status
            $table->enum('Citizen_HousingStatus', [
                'Permanent Resident',
                'Renter'
            ])->nullable();

            // Health and Family Planning
            $table->boolean('Citizen_Pregnant')->default(false);
            $table->boolean('Citizen_InPills')->default(false);

            // Vital Status
            $table->boolean('Citizen_IsDeceased')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citizens');
    }
};
