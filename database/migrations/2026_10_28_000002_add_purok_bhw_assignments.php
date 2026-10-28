<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purok_user')) {
            Schema::create('purok_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purok_id')->constrained('puroks')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['purok_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purok_user');
    }
};
