<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('citizens', 'public_health_id_enabled')) {
            DB::table('citizens')->update(['public_health_id_enabled' => true]);
        }
    }

    public function down(): void
    {
    }
};
