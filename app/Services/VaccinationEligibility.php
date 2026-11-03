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
                    includeSchedules: true
                )
                : [
                    'eligible' => false,
                    'status' => 'unavailable',
                    'reason' => 'All configured routine doses are already recorded or scheduled.',
                ];

            $options[$code] = [
                'label' => $vaccine['label'],
                'guideline' => $vaccine['guideline'],
                'source' => $vaccine['source'],
                'next_dose' => $nextDose['name'] ?? null,
                'eligible' => $result['eligible'],
                'status' => $result['status'],
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
        ?string $vaccineCode = null,
        bool $useExplicitVaccineCode = false
    ): array {
        $administeredOn = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $birthDate = $citizen->Citizen_BirthDate
            ? Carbon::parse($citizen->Citizen_BirthDate)
            : null;

        if (($birthDate && $administeredOn->lt($birthDate)) || (!$allowFutureDate && $administeredOn->isFuture())) {
            return [
                'eligible' => false,
                'status' => 'unavailable',
                'reason' => 'The administration date must be on or after birth and not in the future.',
            ];
        }

        $code = $useExplicitVaccineCode ? $vaccineCode : ($vaccineCode ?? $this->codeFor($vaccineName));
        $vaccine = $code ? ($this->vaccines()[$code] ?? null) : null;

        if (!$vaccine) {
            return [
                'eligible' => false,
                'status' => 'unknown',
                'reason' => 'No validated age rule is configured for this recognized vaccine.',
            ];
        }

        if (!$birthDate) {
            return [
                'eligible' => false,
                'status' => 'unknown',
                'reason' => 'A valid date of birth is required to determine age eligibility.',
            ];
        }

        $doseIndex = null;
        foreach ($vaccine['doses'] as $index => $dose) {
            if ($dose['name'] === $doseName) {
                $doseIndex = $index;
                break;
            }
        }

        if ($doseIndex === null) {
            return ['eligible' => false, 'status' => 'unavailable', 'reason' => 'Select a valid dose for this vaccine.'];
        }

        if ($checkSequence) {
            $nextDose = $this->nextDose($citizen, $code, $includeSchedules);
            if (($nextDose['name'] ?? null) !== $doseName) {
                return [
                    'eligible' => false,
                    'status' => 'unavailable',
                    'reason' => 'Doses must be recorded in sequence; select the next due dose.',
                ];
            }
        }

        $dose = $vaccine['doses'][$doseIndex];
        $ageDays = $birthDate->diffInDays($administeredOn);
        $ageMonths = $birthDate->diffInMonths($administeredOn);
        $ageYears = $birthDate->diffInYears($administeredOn);

        foreach ([
            'days' => $ageDays,
            'months' => $ageMonths,
            'years' => $ageYears,
        ] as $unit => $age) {
            $minKey = "min_{$unit}";
            $maxKey = "max_{$unit}";
            if (isset($dose[$minKey]) && $age < $dose[$minKey]) {
                return [
                    'eligible' => false,
                    'status' => 'unavailable',
                    'reason' => $this->ageRangeMessage($dose, $unit),
                ];
            }
            if (isset($dose[$maxKey]) && $age > $dose[$maxKey]) {
                return [
                    'eligible' => false,
                    'status' => 'unavailable',
                    'reason' => $this->ageRangeMessage($dose, $unit),
                ];
            }
        }

        return ['eligible' => true, 'status' => 'available', 'reason' => $vaccine['guideline']];
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
        $normalize = static function (string $value): string {
            $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower(trim($value)));

            return trim(is_string($normalized) ? $normalized : '');
        };
        $normalizedName = $normalize($name);
        $aliases = config('medical_terminology.vaccine_aliases', []);

        foreach ($this->vaccines() as $code => $vaccine) {
            foreach (array_merge([$code, $vaccine['label']], $aliases[$code] ?? []) as $term) {
                if ($normalizedName !== '' && $normalizedName === $normalize((string) $term)) {
                    return (string) $code;
                }
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

}
