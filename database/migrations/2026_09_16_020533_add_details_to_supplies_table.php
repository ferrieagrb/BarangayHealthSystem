<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            if (!Schema::hasColumn('supplies', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('min_stock');
            }
            if (!Schema::hasColumn('supplies', 'supplier')) {
                $table->string('supplier')->nullable()->after('expiration_date');
            }
            if (!Schema::hasColumn('supplies', 'description')) {
                $table->text('description')->nullable()->after('supplier');
            }
            if (!Schema::hasColumn('supplies', 'status')) {
                $table->string('status')->default('Available')->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn([
                'expiration_date',
                'supplier',
                'description',
                'status'
            ]);
        });
    }
};