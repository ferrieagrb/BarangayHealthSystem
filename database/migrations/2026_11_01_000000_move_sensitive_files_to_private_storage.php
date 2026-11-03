<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        DB::table('citizens')->whereNotNull('photo_path')->orderBy('id')->each(function ($row) use ($public, $private) {
            $path = ltrim($row->photo_path, '/');

            if ($public->exists($path)) {
                $private->put($path, $public->get($path));
                $public->delete($path);
            }
        });

        DB::table('referrals')->whereNotNull('file_path')->where('file_path', 'like', 'storage/referrals/%')->orderBy('id')->each(function ($row) use ($private) {
            $legacy = public_path($row->file_path);
            $newPath = 'referrals/' . basename($row->file_path);

            if (is_file($legacy)) {
                $private->put($newPath, file_get_contents($legacy));
                @unlink($legacy);
            }

            DB::table('referrals')->where('id', $row->id)->update(['file_path' => $newPath]);
        });
    }

    public function down(): void
    {
        // Files intentionally remain private.
    }
};
