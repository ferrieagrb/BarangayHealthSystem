<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('puroks', function (Blueprint $table) {
            if (!Schema::hasColumn('puroks', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('puroks', 'boundary')) {
                $table->json('boundary')->nullable();
            }
        });

        Schema::table('purok_subgroups', function (Blueprint $table) {
            if (!Schema::hasColumn('purok_subgroups', 'description')) {
                $table->text('description')->nullable();
            }

            if (!Schema::hasColumn('purok_subgroups', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('purok_subgroups', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }

            if (!Schema::hasColumn('purok_subgroups', 'boundary')) {
                $table->json('boundary')->nullable();
            }
        });

        if (!Schema::hasTable('purok_subgroup_user')) {
            Schema::create('purok_subgroup_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purok_subgroup_id')
                    ->constrained('purok_subgroups')
                    ->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['purok_subgroup_id', 'user_id']);
            });
        }

        if (Schema::hasColumn('purok_subgroups', 'user_id')) {
            DB::table('purok_subgroups')
                ->join('users', 'users.id', '=', 'purok_subgroups.user_id')
                ->whereNotNull('purok_subgroups.user_id')
                ->where('users.role', 'bhw')
                ->orderBy('purok_subgroups.id')
                ->select(['purok_subgroups.id', 'purok_subgroups.user_id'])
                ->chunk(500, function ($subgroups) {
                    $now = now();
                    $assignments = $subgroups->map(fn ($subgroup) => [
                        'purok_subgroup_id' => $subgroup->id,
                        'user_id' => $subgroup->user_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('purok_subgroup_user')->insertOrIgnore($assignments);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purok_subgroup_user');

        Schema::table('purok_subgroups', function (Blueprint $table) {
            $columns = array_filter(
                ['description', 'latitude', 'longitude', 'boundary'],
                fn (string $column) => Schema::hasColumn('purok_subgroups', $column)
            );

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('puroks', function (Blueprint $table) {
            $columns = array_filter(
                ['description', 'boundary'],
                fn (string $column) => Schema::hasColumn('puroks', $column)
            );

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
