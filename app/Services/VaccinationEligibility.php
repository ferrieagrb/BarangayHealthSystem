<?php

namespace App\Services;

use App\Models\VaccinationRecord;
use App\Models\VaccinationSchedule;
use App\Models\citizens;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class VaccinationEligibility
{
    public function vaccines(): array
    {
        return config('vaccination.vaccines', []);
    }

    public function optionsFor(citizens $citizen, ?CarbonInterface $date = null): array
    {
        $date ??= now();
        $options = [];

        foreach ($this->vaccines() as $code => $vaccine) {
            $nextDose = $this->nextDose($citizen, $code, includeSchedules: true);
            $result = $nextDose
                ? $this->evaluate(
                    $citizen,
                    $code,
                    $nextDose['name'],
                    $date,
                    includeSchedules: true,
                    allowCatchUp: true
                )
                : ['eligible' => false, 'review_required' => false, 'reason' => 'All configured routine doses are already recorded or scheduled.'];

            $options[$code] = [
                'label' => $vaccine['label'],
                'guideline' => $vaccine['guideline'],
                'source' => $vaccine['source'],
                'next_dose' => $nextDose['name'] ?? null,
                'eligible' => $result['eligible'],
                'review_required' => $result['review_required'] ?? false,
                'reason' => $result['reason'],
                'doses' => $vaccine['doses'],
            ];
        }

        return $options;
    }

    public function evaluate(
        citizens $citizen,
        string $vaccineName,
        string $doseName,
        CarbonInterface|string $date,
        bool $includeSchedules = false,
        bool $allowFutureDate = false,
        bool $checkSequence = true,
        bool $allowCatchUp = false
    ): array {
        $code = $this->codeFor($vaccineName);
        $vaccine = $code ? ($this->vaccines()[$code] ?? null) : null;

        if (!$vaccine) {
            return ['eligible' => false, 'review_required' => false, 'reason' => 'Select a vaccine in the configured routine schedule.'];
        }

        if (!$citizen->Citizen_BirthDate) {
            return ['eligible' => false, 'review_required' => false, 'reason' => 'A valid date of birth is required to determine vaccine eligibility.'];
        }

        $doseIndex = null;
        foreach ($vaccine['doses'] as $index => $dose) {
            if ($dose['name'] === $doseName) {
                $doseIndex = $index;
                break;
            }
        }

        if ($doseIndex === null) {
            return ['eligible' => false, 'review_required' => false, 'reason' => 'Select a valid dose for this vaccine.'];
        }

        if ($checkSequence) {
            $nextDose = $this->nextDose($citizen, $code, $includeSchedules);
            if (($nextDose['name'] ?? null) !== $doseName) {
                return ['eligible' => false, 'review_required' => false, 'reason' => 'Doses must be recorded in sequence; select the next due dose.'];
            }
        }

        $administeredOn = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $birthDate = Carbon::parse($citizen->Citizen_BirthDate);
        if ($administeredOn->lt($birthDate) || (!$allowFutureDate && $administeredOn->isFuture())) {
            return ['eligible' => false, 'review_required' => false, 'reason' => 'The administration date must be on or after birth and not in the future.'];
        }

        $dose = $vaccine['doses'][$doseIndex];
        $ageDays = $birthDate->diffInDays($administeredOn);
        $ageMonths = $birthDate->diffInMonths($administeredOn);
        $ageYears = $birthDate->diffInYears($administeredOn);

        $outsideRoutineWindow = false;
        $minimumAgeMet = true;
        foreach ([
            'days' => $ageDays,
            'months' => $ageMonths,
            'years' => $ageYears,
        ] as $unit => $age) {
            $minKey = "min_{$unit}";
            $maxKey = "max_{$unit}";
            if (isset($dose[$minKey]) && $age < $dose[$minKey]) {
                $minimumAgeMet = false;
            }
            if (isset($dose[$maxKey]) && $age > $dose[$maxKey]) {
                $outsideRoutineWindow = true;
            }
        }

        if ($minimumAgeMet && $outsideRoutineWindow && $allowCatchUp) {
            return [
                'eligible' => true,
                'review_required' => true,
                'reason' => 'Catch-up candidate only. Confirm the vaccine and dose schedule with the health center before recording.',
            ];
        }

        if (!$minimumAgeMet || $outsideRoutineWindow) {
            return [
                'eligible' => false,
                'review_required' => false,
                'reason' => $this->ageRangeMessage($dose, $minimumAgeMet ? $this->firstExceededMaximumUnit($dose, $ageDays, $ageMonths, $ageYears) : $this->firstUnmetMinimumUnit($dose, $ageDays, $ageMonths, $ageYears)),
            ];
        }

        return ['eligible' => true, 'review_required' => false, 'reason' => $vaccine['guideline']];
    }

    public function nextDose(citizens $citizen, string $code, bool $includeSchedules = false): ?array
    {
        $vaccine = $this->vaccines()[$code] ?? null;
        if (!$vaccine) {
            return null;
        }

        $recorded = $citizen->vaccinations()
            ->get()
            ->filter(fn (VaccinationRecord $record) => $this->codeFor($record->vaccine_name) === $code)
            ->pluck('dose_number')
            ->all();

        if ($includeSchedules) {
            $scheduled = $citizen->vaccinationSchedules()
                ->where('status', 'scheduled')
                ->get()
                ->filter(fn (VaccinationSchedule $schedule) => $this->codeFor($schedule->vaccine_name) === $code)
                ->pluck('dose_number')
                ->all();
            $recorded = array_merge($recorded, $scheduled);
        }

        foreach ($vaccine['doses'] as $dose) {
            if (!in_array($dose['name'], $recorded, true)) {
                return $dose;
            }
        }

        return null;
    }

    public function codeFor(string $name): ?string
    {
        foreach ($this->vaccines() as $code => $vaccine) {
            if (strcasecmp(trim($name), trim($vaccine['label'])) === 0 || strcasecmp(trim($name), $code) === 0) {
                return $code;
            }
        }

        return null;
    }

    private function ageRangeMessage(array $dose, string $unit): string
    {
        $min = $dose["min_{$unit}"] ?? null;
        $max = $dose["max_{$unit}"] ?? null;
        if ($min === $max) {
            $range = "age {$min} {$unit}";
        } elseif ($max === null) {
            $range = "age {$min} {$unit} and above";
        } else {
            $range = "ages {$min}-{$max} {$unit}";
        }

        return "This dose is configured for {$range}. " . config('vaccination.outside_routine_age_message');
    }

    private function firstExceededMaximumUnit(array $dose, int|float $days, int|float $months, int|float $years): string
    {
        foreach (['days' => $days, 'months' => $months, 'years' => $years] as $unit => $age) {
            if (isset($dose["max_{$unit}"]) && $age > $dose["max_{$unit}"]) {
                return $unit;
            }
        }

        return 'years';
    }

    private function firstUnmetMinimumUnit(array $dose, int|float $days, int|float $months, int|float $years): string
    {
        foreach (['days' => $days, 'months' => $months, 'years' => $years] as $unit => $age) {
            if (isset($dose["min_{$unit}"]) && $age < $dose["min_{$unit}"]) {
                return $unit;
            }
        }

        return 'days';
    }
}
