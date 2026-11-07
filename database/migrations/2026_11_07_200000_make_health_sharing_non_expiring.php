<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citizen_health_shares', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->change();
        });
        // Default sharing on for existing citizens, preserving their latest explicit off choice.
        DB::table('citizens')->whereNull('deleted_at')->select('id')->orderBy('id')->chunkById(200, function ($citizens) {
            foreach ($citizens as $citizen) {
                DB::transaction(function () use ($citizen) {
                    DB::table('citizens')->where('id', $citizen->id)->lockForUpdate()->first();
                    $latest = DB::table('citizen_health_shares')->where('citizen_id', $citizen->id)
                        ->orderByDesc('granted_at')->orderByDesc('id')->first();
                    if ($latest && $latest->revoked_at !== null) {
                        DB::table('citizen_health_shares')->where('citizen_id', $citizen->id)
                            ->whereNull('revoked_at')->update(['revoked_at' => now(), 'updated_at' => now()]);
                        return;
                    }
                    if ($latest) {
                        DB::table('citizen_health_shares')->where('id', $latest->id)
                            ->update(['expires_at' => null, 'updated_at' => now()]);
                    } else {
                        DB::table('citizen_health_shares')->insert([
                            'citizen_id' => $citizen->id, 'granted_by_user_id' => null,
                            'granted_at' => now(), 'expires_at' => null, 'revoked_at' => null,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                });
            }
        });
    }

    public function down(): void
    {
        DB::table('citizen_health_shares')->whereNull('expires_at')
            ->update(['expires_at' => now()->addDays(7), 'updated_at' => now()]);
        Schema::table('citizen_health_shares', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable(false)->change();
        });
    }
};
