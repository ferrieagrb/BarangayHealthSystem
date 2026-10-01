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
    Schema::create('families', function (Blueprint $table) {
        $table->id();
        $table->string('family_name'); // e.g., "Santos Family"
        $table->foreignId('purok_id')->constrained('puroks')->onDelete('cascade');
        $table->foreignId('subgroup_id')->nullable()->constrained('purok_subgroups')->onDelete('set null');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('families');
    }
};
