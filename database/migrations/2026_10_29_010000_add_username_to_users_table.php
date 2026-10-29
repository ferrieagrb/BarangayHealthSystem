<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 50)->nullable()->unique()->after('email');
            });
        }

        // Existing citizen accounts use their contact number as the username when it is unique.
        $taken = [];
        $citizens = DB::table('users')
            ->join('citizens', 'citizens.id', '=', 'users.citizen_id')
            ->where('users.role', 'citizen')
            ->whereNull('users.username')
            ->whereNotNull('citizens.Citizen_ContactNo')
            ->orderBy('users.id')
            ->get(['users.id as user_id', 'citizens.Citizen_ContactNo as contact']);

        foreach ($citizens as $row) {
            $username = preg_replace('/[\s\-().]/', '', trim((string) $row->contact));

            if ($username === '' || strlen($username) > 50 || str_contains($username, '@') || isset($taken[$username])) {
                continue;
            }

            if (DB::table('users')->where('username', $username)->exists()) {
                continue;
            }

            DB::table('users')->where('id', $row->user_id)->update(['username' => $username]);
            $taken[$username] = true;
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            });
        }
    }
};
