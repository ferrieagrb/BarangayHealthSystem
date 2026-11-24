<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->enum('status', ['approved', 'released', 'returned'])
                ->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        // Retain generated referrals without an approval status and preserve legacy values.
    }
};
