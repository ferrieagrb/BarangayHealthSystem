<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supply_releases', function (Blueprint $table) {
            $table->string('status')->default('scheduled')->after('claim_date');
            $table->timestamp('claimed_at')->nullable()->after('status');
            $table->foreignId('claimed_by_user_id')->nullable()->after('claimed_at')->constrained('users')->nullOnDelete();
        });

        DB::table('supply_releases')->whereNull('status')->update(['status' => 'claimed']);
    }

    public function down(): void
    {
        Schema::table('supply_releases', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_user_id']);
            $table->dropColumn(['status', 'claimed_at', 'claimed_by_user_id']);
        });
    }
};