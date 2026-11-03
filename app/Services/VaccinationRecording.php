<?php

namespace App\Services;

use App\Models\User;
use App\Models\VaccinationRecord;
use App\Models\citizens;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VaccinationRecording
{
    public function __construct(
        private readonly VaccinationEligibility $eligibility,
        private readonly MedicalTerminologySearch $terminology
    ) {
    }

    public function record(citizens $citizen, User $user, array $data, bool $includeSchedules = true): VaccinationRecord
    {
        $name = trim((string) $data['vaccine_name']);
        $selectionToken = trim((string) ($data['vaccine_selection_token'] ?? ''));
        $selection = $selectionToken !== ''
            ? $this->terminology->resolveVaccineSelection($selectionToken)
            : null;

        if ($selectionToken !== '' && !$selection) {
            $this->reject('vaccine_name', 'Select a vaccine from the current CDC terminology suggestions.');
        }

        $vaccineCode = $selection
            ? $selection['vaccine_code']
            : $this->localCodeFor($name);
        $cvxCode = $selection['cvx_code'] ?? null;
        $formulation = $selection['formulation'] ?? null;
        if ($selection) {
            if ($vaccineCode !== null) {
                if ($this->localCodeFor($name) !== $vaccineCode) {
                    $this->reject('vaccine_name', 'The selected vaccine does not match its verified BHEAMS mapping.');
                }
                $name = $this->eligibility->vaccines()[$vaccineCode]['label'];
            } elseif (!$this->sameTerm($name, $selection['short_name'])
                && !$this->sameTerm($name, $selection['official_name'])
            ) {
                $this->reject('vaccine_name', 'The selected vaccine does not match its CDC CVX terminology record.');
            } else {
                $name = $selection['short_name'];
            }
        } elseif ($vaccineCode !== null) {
            $name = $this->eligibility->vaccines()[$vaccineCode]['label'];
        } elseif ($vaccineCode === null) {
            $this->reject('vaccine_name', 'Select a recognized vaccine from the dropdown or CDC suggestions.');
        }

        $historical = filter_var($data['historical_recording'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $result = $this->eligibility->evaluate(
            $citizen,
            $name,
            trim((string) $data['dose_number']),
            (string) $data['date_administered'],
            includeSchedules: $includeSchedules,
            checkSequence: !$historical,
            vaccineCode: $vaccineCode,
            useExplicitVaccineCode: $selection !== null
        );

        if (!$result['eligible'] && !$historical) {
            $this->reject('vaccine_name', $result['reason']);
        }

        $reviewReason = $result['eligible']
            ? null
            : ($selection['eligibility_reason'] ?? $result['reason']);

        return DB::transaction(function () use (
            $citizen,
            $user,
            $data,
            $name,
            $cvxCode,
            $formulation,
            $historical,
            $result,
            $reviewReason
        ): VaccinationRecord {
            citizens::query()->whereKey($citizen->id)->lockForUpdate()->firstOrFail();

            $duplicate = VaccinationRecord::query()
                ->where('citizen_id', $citizen->id)
                ->where('dose_number', trim((string) $data['dose_number']))
                ->whereDate('date_administered', $data['date_administered'])
                ->where(function ($query) use ($cvxCode, $name) {
                    if ($cvxCode !== null) {
                        $query->where('cvx_code', $cvxCode)
                            ->orWhere('vaccine_name', $name);
                    } else {
                        $query->where('vaccine_name', $name);
                    }
                })
                ->exists();

            if ($duplicate) {
                $this->reject('vaccine_name', 'This dose is already recorded for this citizen on that date.');
            }

            return VaccinationRecord::create([
                'citizen_id' => $citizen->id,
                'vaccine_name' => $name,
                'dose_number' => trim((string) $data['dose_number']),
                'date_administered' => $data['date_administered'],
                'administered_by' => $user->name,
                'cvx_code' => $cvxCode,
                'vaccine_formulation' => $formulation,
                'historical_recording' => $historical,
                'eligibility_review_required' => !$result['eligible'],
                'eligibility_review_reason' => $reviewReason,
                'catch_up_reviewed' => false,
            ]);
        });
    }

    private function sameTerm(string $left, string $right): bool
    {
        $normalize = static function (string $value): string {
            $normalized = preg_replace('/[^\p{L}\p{N}]+/u', ' ', mb_strtolower(trim($value)));

            return trim(is_string($normalized) ? $normalized : '');
        };

        return $normalize($left) !== '' && $normalize($left) === $normalize($right);
    }

    private function localCodeFor(string $name): ?string
    {
        foreach ($this->eligibility->vaccines() as $code => $vaccine) {
            if ($this->sameTerm($name, (string) $code) || $this->sameTerm($name, $vaccine['label'])) {
                return (string) $code;
            }
        }

        return null;
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
