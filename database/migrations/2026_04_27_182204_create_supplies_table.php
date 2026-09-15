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
            $table->string('name');
            $table->string('item_number')->unique()->nullable();
            $table->string('serial_number')->nullable();
            $table->string('category');
            $table->string('unit')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('min_stock')->default(5);
            $table->date('expiration_date')->nullable();
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('Available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};