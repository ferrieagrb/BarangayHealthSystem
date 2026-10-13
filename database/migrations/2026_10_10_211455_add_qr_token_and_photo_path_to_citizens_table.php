<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    
public function up(): void
{
    Schema::table('citizens', function (Blueprint $table) {
        $table->uuid('qr_token')->nullable()->unique();
        $table->string('photo_path')->nullable();
    });

    // Generate tokens for citizens who already exist.
    DB::table('citizens')
        ->whereNull('qr_token')
        ->orderBy('id')
        ->chunkById(100, function ($citizens) {
            foreach ($citizens as $citizen) {
                DB::table('citizens')
                    ->where('id', $citizen->id)
                    ->update([
                        'qr_token' => (string) Str::uuid(),
                    ]);
            }
        });
}

public function down(): void
{
    Schema::table('citizens', function (Blueprint $table) {
        $table->dropUnique(['qr_token']);
        $table->dropColumn(['qr_token', 'photo_path']);
    });
}

};
