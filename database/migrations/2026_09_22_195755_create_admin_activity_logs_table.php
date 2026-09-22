<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('action'); // e.g., 'system.settings_updated', 'admin.promoted'
            $table->string('scope')->default('governance'); // governance, infrastructure, security
            $table->text('description'); // Human-readable summary
            $table->string('target_type')->nullable(); // Model class if applicable
            $table->unsignedBigInteger('target_id')->nullable(); // ID of target record
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
    }
};