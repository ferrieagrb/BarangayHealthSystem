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
    Schema::create('health_record_activity_logs', function (Blueprint $table) {
        $table->id();

        $table->string('action'); // view, create, update, delete
        $table->unsignedBigInteger('citizen_id')->nullable();
        $table->unsignedBigInteger('health_record_id')->nullable();

        $table->text('description')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_record_activity_logs');
    }
};
