<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('announcements', 'user_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('category')->constrained()->nullOnDelete();
            });
        }

        Schema::create('announcement_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_images');

        if (Schema::hasColumn('announcements', 'user_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};