<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id(); // primary key of health record

            $table->unsignedBigInteger('citizen_id'); // foreign key

            $table->text('diagnosis');

            $table->timestamps(); // created_at & updated_at

            // foreign key constraint
            $table->foreign('citizen_id')
                  ->references('id')
                  ->on('citizens')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
