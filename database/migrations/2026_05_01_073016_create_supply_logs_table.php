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
    Schema::create('supply_logs', function (Blueprint $table) {
        $table->id();

        // what happened
        $table->string('action'); // deposit | release

        // item involved
        $table->foreignId('supply_id')->constrained()->onDelete('cascade');

        // quantity moved
        $table->integer('quantity');

        // who performed the action (user account)
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // who received / released to (citizen)
        $table->foreignId('citizen_id')->nullable()->constrained()->onDelete('set null');

        // optional notes / diagnosis link
        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supply_logs');
    }
};
