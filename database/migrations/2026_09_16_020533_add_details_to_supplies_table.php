<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->string('item_number')->unique()->nullable()->after('name');
            $table->string('serial_number')->nullable()->after('item_number');
            $table->string('unit')->nullable()->after('category');
            $table->integer('min_stock')->default(5)->after('quantity');
            $table->date('expiration_date')->nullable()->after('min_stock');
            $table->string('supplier')->nullable()->after('expiration_date');
            $table->text('description')->nullable()->after('supplier');
            $table->string('status')->default('Available')->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn([
                'item_number',
                'serial_number',
                'unit',
                'min_stock',
                'expiration_date',
                'supplier',
                'description',
                'status'
            ]);
        });
    }
};