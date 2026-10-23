<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccination_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('citizen_id')->constrained('citizens')->onDelete('cascade');
            $table->string('vaccine_name');
            $table->string('dose_number')->nullable();
            $table->date('scheduled_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccination_schedules');
    }
};