<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('puroks', function (Blueprint $table) {
        // 1. Drop the foreign key constraint first
        $table->dropForeign(['user_id']);
        
        // 2. Then drop the column safely
        $table->dropColumn('user_id');
    });

    Schema::table('purok_subgroups', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable()->after('id');
        // Optional foreign key for the new subgroup column:
        // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('purok_subgroups', function (Blueprint $table) {
        // Drop foreign if you added it, then drop column
        // $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });

    Schema::table('puroks', function (Blueprint $table) {
        $table->unsignedBigInteger('user_id')->nullable();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
    });
}
};
