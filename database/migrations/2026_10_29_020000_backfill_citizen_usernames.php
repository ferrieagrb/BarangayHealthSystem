<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('users')
            ->join('citizens', 'citizens.id', '=', 'users.citizen_id')
            ->where('users.role', 'citizen')
            ->orderBy('users.id')
            ->get(['users.id as user_id', 'citizens.Citizen_LName as last', 'citizens.Citizen_BirthDate as birth']);

        // Clear first so regenerated names never collide with old contact-number usernames.
        DB::table('users')->whereIn('id', $rows->pluck('user_id'))->update(['username' => null]);

        foreach ($rows as $row) {
            $username = User::citizenUsername($row->last, $row->birth);
            if ($username) {
                DB::table('users')->where('id', $row->user_id)->update(['username' => $username]);
            }
        }
    }

    public function down(): void
    {
    }
};
