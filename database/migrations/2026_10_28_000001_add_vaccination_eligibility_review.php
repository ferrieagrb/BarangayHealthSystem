<?php

use App\Models\VaccinationRecord;
use App\Services\VaccinationEligibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->boolean('eligibility_review_required')->default(false);
            $table->string('eligibility_review_reason')->nullable();
            $table->boolean('catch_up_reviewed')->default(false);
        });

        if (Schema::hasTable('vaccination_schedules')) {
            Schema::table('vaccination_schedules', function (Blueprint $table) {
                $table->boolean('catch_up_reviewed')->default(false);
            });
        }

        $eligibility = app(VaccinationEligibility::class);
        VaccinationRecord::with('citizen')->chunkById(100, function ($records) use ($eligibility): void {
            foreach ($records as $record) {
                $result = $record->citizen
                    ? $eligibility->evaluate(
                        $record->citizen,
                        $record->vaccine_name,
                        $record->dose_number,
                        $record->date_administered,
                        checkSequence: false
                    )
                    : ['eligible' => false, 'reason' => 'The linked citizen record could not be found.'];

                if (!$result['eligible']) {
                    $record->forceFill([
                        'eligibility_review_required' => true,
                        'eligibility_review_reason' => $result['reason'],
                    ])->saveQuietly();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('vaccination_records', function (Blueprint $table) {
            $table->dropColumn(['eligibility_review_required', 'eligibility_review_reason', 'catch_up_reviewed']);
        });

        if (Schema::hasTable('vaccination_schedules')) {
            Schema::table('vaccination_schedules', function (Blueprint $table) {
                $table->dropColumn('catch_up_reviewed');
            });
        }
    }
};
