<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('supply_releases')
            ->where('status', 'scheduled')
            ->update([
                'status' => 'claimed',
                'claimed_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        DB::table('supply_releases')
            ->where('status', 'claimed')
            ->whereNotNull('claimed_at')
            ->update([
                'status' => 'scheduled',
                'claimed_at' => null,
                'claimed_by_user_id' => null,
            ]);
    }
};