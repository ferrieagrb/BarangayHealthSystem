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
            Schema::create('vaccination_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('citizen_id')->constrained('citizens')->onDelete('cascade');
                $table->string('vaccine_name');
                $table->string('dose_number');
                $table->date('date_administered');
                $table->string('administered_by');
                $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_records');
    }
};
