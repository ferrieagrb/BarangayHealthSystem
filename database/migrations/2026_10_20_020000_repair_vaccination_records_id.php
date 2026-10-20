<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vaccination_records')) {
            return;
        }

        DB::statement(
            'ALTER TABLE `vaccination_records` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)'
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('vaccination_records')) {
            return;
        }

        DB::statement('ALTER TABLE `vaccination_records` DROP PRIMARY KEY, MODIFY `id` BIGINT UNSIGNED NOT NULL');
    }
};
