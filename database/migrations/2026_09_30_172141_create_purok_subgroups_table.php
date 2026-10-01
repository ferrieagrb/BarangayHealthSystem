<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('purok_subgroups', function (Blueprint $table) {
        $table->id();
        $table->foreignId('purok_id')->constrained('puroks')->onDelete('cascade');
        $table->string('name'); // e.g., P1A, P1B
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purok_subgroups');
    }
};
