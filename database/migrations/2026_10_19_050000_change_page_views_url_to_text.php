<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_views')) {
            DB::statement('ALTER TABLE page_views MODIFY url TEXT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('page_views')) {
            DB::statement('ALTER TABLE page_views MODIFY url VARCHAR(255) NULL');
        }
    }
};
