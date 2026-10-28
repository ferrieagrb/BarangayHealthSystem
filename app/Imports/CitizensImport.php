<?php

namespace App\Imports;

use App\Models\citizens;
use App\Models\Purok;
use App\Models\PurokSubgroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CitizensImport implements ToModel, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    private const NAME_PATTERN = '/^[\p{L}\p{M}][\p{L}\p{M}\s.\'\-]*$/u';
    private const DATE_FORMATS = ['Y-m-d', 'Y/m/d', 'n/j/Y', 'n-j-Y', 'n.j.Y', 'F j, Y', 'F j Y', 'M j, Y', 'M j Y', 'j F Y', 'j M Y'];
    private const MAX_ISSUES = 25;

    public int $createdAccounts = 0;
    public int $skippedRows = 0;
    public int $ignoredValues = 0;
    /** @var list<string> */
    public array $issues = [];
    private int $entryNumber = 0;
    private ?\Illuminate\Support\Collection $puroks = null;
    private string $hashedPassword;

    public function __construct()
    {
        $this->hashedPassword = Hash::make('Barangay123!', ['rounds' => 8]);
    }

    public function model(array $row)
    {
        $firstName  = $this->text($row['first_name'] ?? $row['First Name'] ?? $row['FIRST_NAME'] ?? '');
        $lastName   = $this->text($row['last_name'] ?? $row['Last Name'] ?? $row['LAST_NAME'] ?? '');
        $purokInput = $this->text($row['purok'] ?? $row['Purok'] ?? $row['PUROK'] ?? '');

        if ($firstName === '' || $lastName === '' || $purokInput === '' || strcasecmp($firstName, 'first_name') === 0) {
            return null;
        }

        $this->entryNumber++;
        $entry = "Entry {$this->entryNumber} ({$firstName} {$lastName})";

        // Required values must be valid, otherwise the row cannot be imported at all.
        foreach (['First name' => $firstName, 'Last name' => $lastName] as $label => $value) {
            if (!$this->validName($value)) {
                $this->skipRow("{$entry}: {$label} \"{$value}\" has an invalid format");
                return null;
            }
        }

        $purok = $this->findPurok($purokInput);
        if (!$purok) {
            $this->skipRow("{$entry}: Purok \"{$purokInput}\" does not match any existing purok");
            return null;
        }

        // Optional values: a wrongly formatted value is dropped, the rest of the row is still imported.
        $middleName = $this->text($row['middle_name'] ?? $row['Middle Name'] ?? $row['MIDDLE_NAME'] ?? '');
        if ($middleName !== '' && !$this->validName($middleName)) {
            $this->ignoreValue("{$entry}: Middle name \"{$middleName}\" has an invalid format and was left blank");
            $middleName = '';
        }

        $birthDateValue = $row['birthdate'] ?? $row['Birthdate'] ?? $row['BIRTHDATE'] ?? null;
        $birthDate = null;
        $age = null;
        if ($birthDateValue !== null && $this->text($birthDateValue) !== '') {
            $birthDate = $this->parseBirthDate($birthDateValue);
            if ($birthDate === null) {
                $this->ignoreValue("{$entry}: Birthdate \"{$this->text($birthDateValue)}\" is not a valid date and was left blank");
            } else {
                $age = Carbon::parse($birthDate)->age;
            }
        }

        $sexInput = $this->text($row['sex'] ?? $row['Sex'] ?? $row['SEX'] ?? '');
        $sex = null;
        if ($sexInput !== '') {
            $sex = match (strtolower($sexInput)) {
                'male', 'm' => 'Male',
                'female', 'f' => 'Female',
                default => null,
            };
            if ($sex === null) {
                $this->ignoreValue("{$entry}: Sex \"{$sexInput}\" must be Male or Female and was left blank");
            }
        }

        $subPurokInput = $this->text($row['sub_purok'] ?? $row['subpurok'] ?? $row['Sub Purok'] ?? $row['SubPurok'] ?? $row['subgroup'] ?? '');
        $subgroup = null;
        if ($subPurokInput !== '') {
            $subgroup = $this->findSubgroup($purok, $subPurokInput);
            if (!$subgroup) {
                $this->ignoreValue("{$entry}: Sub purok \"{$subPurokInput}\" was not found under {$purok->name} and was left blank");
            }
        } else {
            // Fallback to the first available subgroup of the purok if not specified
            $subgroup = $purok->subgroups->first();
        }

        $purokName = $purok->name;

        return DB::transaction(function () use ($firstName, $lastName, $middleName, $birthDate, $age, $sex, $purokName, $subgroup) {
            $citizen = citizens::create([
                'Citizen_FName'     => $firstName,
                'Citizen_MName'     => $middleName,
                'Citizen_LName'     => $lastName,
                'Citizen_BirthDate' => $birthDate,
                'Citizen_Age'       => $age,
                'Citizen_ContactNo' => '',
                'Citizen_Sex'       => $sex,
                'Citizen_Purok'     => $purokName,
                'subgroup_id'       => $subgroup?->id,
                'qr_token'          => (string) Str::uuid(),
            ]);

            $emailSlug = Str::slug(trim($firstName . ' ' . $lastName)) ?: 'citizen';
            $email     = $emailSlug . '.' . $citizen->id . '@amuyong.local';

            User::create([
                'name'                 => trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName),
                'email'                => $email,
                'password'             => $this->hashedPassword,
                'role'                 => 'citizen',
                'citizen_id'           => $citizen->id,
                'must_change_password' => true,
            ]);

            $this->createdAccounts++;

            return $citizen;
        });
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function text(mixed $value): string
    {
        if (is_float($value) && floor($value) === $value) {
            $value = (int) $value;
        }

        return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
    }

    private function validName(string $value): bool
    {
        return mb_strlen($value) <= 255 && preg_match(self::NAME_PATTERN, $value) === 1;
    }

    private function parseBirthDate(mixed $value): ?string
    {
        try {
            if (is_numeric($value)) {
                $date = Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } else {
                $date = $this->parseDateString($this->text($value));
            }
        } catch (\Throwable) {
            return null;
        }

        if (!$date || $date->year < 1900 || $date->isFuture()) {
            return null;
        }

        return $date->toDateString();
    }

    private function parseDateString(string $value): ?Carbon
    {
        foreach (self::DATE_FORMATS as $format) {
            $parsed = \DateTime::createFromFormat('!' . $format, $value);
            $errors = \DateTime::getLastErrors();

            // Reject trailing data (e.g. 1/11/11/2026) and overflowed dates (e.g. 2/31/2026).
            if ($parsed && (!$errors || ($errors['warning_count'] === 0 && $errors['error_count'] === 0))) {
                return Carbon::instance($parsed);
            }
        }

        return null;
    }

    private function findPurok(string $input): ?Purok
    {
        $this->puroks ??= Purok::with('subgroups')->get();
        $puroks = $this->puroks;
        $needle = mb_strtolower($input);

        return $puroks->first(fn (Purok $purok) => mb_strtolower($purok->name) === $needle)
            ?? $puroks->first(fn (Purok $purok) => mb_strtolower($purok->name) === 'purok ' . $needle);
    }

    private function findSubgroup(Purok $purok, string $input): ?PurokSubgroup
    {
        $needle = mb_strtolower($input);

        return $purok->subgroups->first(fn (PurokSubgroup $subgroup) => mb_strtolower($subgroup->name) === $needle);
    }

    private function skipRow(string $message): void
    {
        $this->skippedRows++;
        $this->addIssue($message . '; row skipped');
    }

    private function ignoreValue(string $message): void
    {
        $this->ignoredValues++;
        $this->addIssue($message);
    }

    private function addIssue(string $message): void
    {
        if (count($this->issues) < self::MAX_ISSUES) {
            $this->issues[] = $message;
        }
    }
}