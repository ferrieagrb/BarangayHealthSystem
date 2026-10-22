<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements') && !Schema::hasColumn('announcements', 'facebook_post_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->string('facebook_post_id')->nullable()->after('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'facebook_post_id')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('facebook_post_id');
            });
        }
    }
};
