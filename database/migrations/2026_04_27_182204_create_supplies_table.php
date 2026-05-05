<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();

            $table->string('name');          // Item
            $table->string('category');      // Category
            $table->integer('quantity');     // Needed for status logic
            $table->integer('min_stock')->default(5); // threshold

            $table->timestamps(); // last_updated = updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
