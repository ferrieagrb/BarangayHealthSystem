<?php

use App\Services\FamilyCodeGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(FamilyCodeGenerator::class)->renumberExisting();
    }

    public function down(): void
    {
        // Previous family numbers cannot be reconstructed after filling gaps.
    }
};
