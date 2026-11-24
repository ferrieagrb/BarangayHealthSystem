<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->foreignId('citizen_id')->nullable()->constrained('citizens')->nullOnDelete();
            $table->date('birth_date')->nullable();
            $table->string('contact_number')->nullable();
            $table->dateTime('referred_at')->nullable();
            $table->integer('age')->nullable()->change();
            $table->text('requests_for')->nullable()->change();
            $table->string('referred_by')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->dropForeign(['citizen_id']);
            $table->dropColumn(['citizen_id', 'birth_date', 'contact_number', 'referred_at']);
        });
        // Preserve blank doctor fields and unknown ages on existing referrals.
    }
};
