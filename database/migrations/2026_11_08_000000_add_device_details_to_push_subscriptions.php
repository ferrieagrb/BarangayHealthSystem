<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('device_name')->default('Unknown browser/device');
            $table->timestamp('last_seen_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['device_name', 'last_seen_at']);
        });
    }
};