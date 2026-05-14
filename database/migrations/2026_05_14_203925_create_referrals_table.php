<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('referrals', function (Blueprint $table) {
        $table->id();

        $table->date('date_of_referral');
        $table->string('name');
        $table->integer('age');
        $table->string('gender');
        $table->text('address');

        $table->text('requests_for');
        $table->text('vital_signs')->nullable();
        $table->text('treatment_given')->nullable();
        $table->text('medication_given')->nullable();
        $table->text('self_medication')->nullable();
        $table->text('maintenance_schedule')->nullable();

        $table->string('referred_by');

        $table->enum('status', ['approved', 'released', 'returned'])->default('approved');

        $table->string('file_path')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
