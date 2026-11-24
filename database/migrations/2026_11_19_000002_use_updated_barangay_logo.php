<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Apply the new official logo even when an older upload was selected.
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'site_logo'],
            ['value' => 'images/barangay-amuyong.png', 'updated_at' => now()]
        );
    }

    public function down(): void
    {
        // Keep the selected official logo; existing uploaded files are preserved.
    }
};
