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
    // Letters and spaces only; a final Jr. or Sr. may contain a period.
    private const NAME_PATTERN = '/^(?:[\p{L}][\p{L}\p{M}]*(?: [\p{L}][\p{L}\p{M}]*)*(?: (?:jr|sr)\.)?|(?:jr|sr)\.)$/iu';
    private const DATE_FORMATS = ['Y-m-d', 'Y/m/d', 'n/j/Y', 'n-j-Y', 'n.j.Y', 'F j, Y', 'F j Y', 'M j, Y', 'M j Y', 'j F Y', 'j M Y'];
    private const MAX_ISSUES = 25;
    private const PUROK_PATTERN = '/^(purok\s*)?\d+$/i';
    private const SUBGROUP_PATTERN = '/^(?:(?:sub[\s\-]*)?(?:purok|group)\s*)?(?:\d+\s*[-._ ]?\s*)?[a-z]$/i';

    public int $createdAccounts = 0;
    public int $skippedRows = 0;
    public int $ignoredValues = 0;
    /** @var list<string> */
    public array $issues = [];
    private int $entryNumber = 0;
    private ?\Illuminate\Support\Collection $puroks = null;
    private string $hashedPassword;

    private const HEADER_ALIASES = [
        'first_name' => ['first_name', 'firstname', 'first', 'given_name', 'fname'],
        'last_name' => ['last_name', 'lastname', 'last', 'surname', 'family_name', 'lname'],
        'middle_name' => ['middle_name', 'middlename', 'middle', 'mname'],
        'purok' => ['purok'],
        'birthdate' => ['birthdate', 'birth_date', 'birthday', 'date_of_birth', 'dob'],
        'sex' => ['sex', 'gender'],
        'contact_number' => ['contact_number', 'contact_no', 'contact', 'contactnumber', 'mobile', 'mobile_number', 'phone', 'phone_number'],
        'sub_purok' => ['sub_purok', 'subpurok', 'sub_purok_group', 'subpurok_group', 'subgroup', 'sub_group', 'purok_subgroup', 'purok_sub_group', 'sub_purok_no', 'sub_purok_number'],
    ];

    private int $headingRowNumber;

    public function __construct(int $headingRowNumber = 1)
    {
        $this->headingRowNumber = $headingRowNumber;
        $this->hashedPassword = Hash::make('Barangay123!', ['rounds' => 8]);
    }

    public function headingRow(): int
    {
        return $this->headingRowNumber;
    }

    public static function canonicalHeader(mixed $header): ?string
    {
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string) $header))), '_');
        foreach (self::HEADER_ALIASES as $canonical => $aliases) {
            if (in_array($slug, $aliases, true)) {
                return $canonical;
            }
        }

        return null;
    }

    /** Finds the row (within the first 20) holding the required First Name and Last Name headers, in any column order. */
    public static function detectHeadingRow(array $rows): int
    {
        foreach (array_slice($rows, 0, 20, true) as $index => $cells) {
            $found = collect($cells)->map(fn ($cell) => self::canonicalHeader($cell))->filter()->unique();
            if ($found->contains('first_name') && $found->contains('last_name')) {
                return $index + 1;
            }
        }

        return 1;
    }

    public function model(array $row)
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $canonical = self::canonicalHeader($key);
            if ($canonical !== null && !array_key_exists($canonical, $normalized)) {
                $normalized[$canonical] = $value;
            }
        }
        $row = $normalized;

        $firstName  = $this->text($row['first_name'] ?? $row['First Name'] ?? $row['FIRST_NAME'] ?? '');
        $lastName   = $this->text($row['last_name'] ?? $row['Last Name'] ?? $row['LAST_NAME'] ?? '');
        $purokInput = $this->text($row['purok'] ?? $row['Purok'] ?? $row['PUROK'] ?? '');

        if ($firstName === '' || $lastName === '' || strcasecmp($firstName, 'first_name') === 0) {
            return null;
        }

        $this->entryNumber++;
        $entry = "Entry {$this->entryNumber} ({$firstName} {$lastName})";

        // Required values must be valid, otherwise the row cannot be imported at all.
        $middleName = $this->text($row['middle_name'] ?? $row['Middle Name'] ?? $row['MIDDLE_NAME'] ?? '');
        foreach (['First name' => $firstName, 'Last name' => $lastName] as $label => $value) {
            if ($value === '') {
                continue;
            }
            if ($this->looksJumbled($value)) {
                $this->skipRow("{$entry}: {$label} \"{$value}\" looks like jumbled letters");
                return null;
            }
            if (!$this->validName($value)) {
                $this->skipRow("{$entry}: {$label} \"{$value}\" must contain letters and spaces only (a period is allowed only in a final Jr. or Sr.)");
                return null;
            }
        }

        // Purok must look like "Purok 3" and exist; otherwise the citizen is still imported without a purok or subgroup.
        $purok = null;
        if ($purokInput === '') {
            $this->ignoreValue("{$entry}: Purok is empty; imported with no purok or subgroup");
        } elseif (!preg_match(self::PUROK_PATTERN, $purokInput)) {
            $this->ignoreValue("{$entry}: Purok \"{$purokInput}\" must be \"Purok [number]\" or just the number; imported with no purok or subgroup");
        } else {
            $purok = $this->findPurok($purokInput);
            if (!$purok) {
                $this->ignoreValue("{$entry}: Purok \"{$purokInput}\" does not exist; imported with no purok or subgroup");
            }
        }

        // Optional values: a wrongly formatted value is dropped, the rest of the row is still imported.
        if ($middleName !== '' && (!$this->validName($middleName) || $this->looksJumbled($middleName))) {
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

        $contactInput = $this->text($row['contact_number'] ?? $row['contact_no'] ?? $row['contact'] ?? $row['contactnumber'] ?? $row['Contact Number'] ?? '');
        $contact = '';
        if ($contactInput !== '') {
            $contact = $this->normalizeContact($contactInput);
            if ($contact === null) {
                $contact = '';
                $this->ignoreValue("{$entry}: Contact number \"{$contactInput}\" must be a valid mobile number (e.g. 09123456789) and was left blank");
            }
        }

        $subPurokInput = $this->text($row['sub_purok'] ?? $row['subpurok'] ?? $row['Sub Purok'] ?? $row['SubPurok'] ?? $row['subgroup'] ?? '');
        $subgroup = null;
        if ($purok && $subPurokInput !== '') {
            if (!preg_match(self::SUBGROUP_PATTERN, $subPurokInput)) {
                $this->ignoreValue("{$entry}: Sub purok \"{$subPurokInput}\" must be a number followed by a letter (e.g. 3a) and was left blank");
            } else {
                $subgroup = $this->findSubgroup($purok, $subPurokInput);
                if (!$subgroup) {
                    $this->ignoreValue("{$entry}: Sub purok \"{$subPurokInput}\" does not exist under {$purok->name} and was left blank");
                }
            }
        } elseif ($purok) {
            $this->ignoreValue("{$entry}: Sub purok is empty; imported with no subgroup");
        } elseif ($subPurokInput !== '') {
            $this->ignoreValue("{$entry}: Sub purok \"{$subPurokInput}\" was left blank because the purok is missing");
        }

        $purokName = $purok?->name;
        $firstName = mb_convert_case($firstName, MB_CASE_TITLE, 'UTF-8');
        $middleName = mb_convert_case($middleName, MB_CASE_TITLE, 'UTF-8');
        $lastName = mb_convert_case($lastName, MB_CASE_TITLE, 'UTF-8');

        return DB::transaction(function () use ($firstName, $lastName, $middleName, $birthDate, $age, $sex, $purokName, $subgroup, $contact) {
            $citizen = citizens::create([
                'Citizen_FName'     => $firstName,
                'Citizen_MName'     => $middleName,
                'Citizen_LName'     => $lastName,
                'Citizen_BirthDate' => $birthDate,
                'Citizen_Age'       => $age,
                'Citizen_ContactNo' => $contact,
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
                'username'             => User::citizenUsername($lastName, $birthDate),
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

    // Accepts 09XXXXXXXXX, 9XXXXXXXXX (Excel drops the leading zero), 639XXXXXXXXX and +639XXXXXXXXX, with spaces, dashes or parentheses.
    private function normalizeContact(string $value): ?string
    {
        if (!preg_match('/^\+?[0-9\s\-()]+$/', $value)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $value);

        if (preg_match('/^63(9\d{9})$/', $digits, $m) || preg_match('/^0?(9\d{9})$/', $digits, $m)) {
            return '0' . $m[1];
        }

        return null;
    }

    // Heuristic for keyboard mashing such as "asdfghjk", "xqzvkw" or "aBcDeF". Multi-word names ("John Andrew Matthew") are checked word by word.
    private function looksJumbled(string $value): bool
    {
        foreach (preg_split('/[\s.\'\-]+/u', mb_strtolower($value), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (class_exists(\Normalizer::class)) {
                $word = preg_replace('/\p{M}+/u', '', \Normalizer::normalize($word, \Normalizer::FORM_D) ?: $word);
            }
            $letters = preg_replace('/[^a-z]/', '', $word);
            if ($letters === '') {
                continue;
            }
            // Doubled letters are normal ("Matthew", "Anne"); three in a row are not.
            if (preg_match('/(.)\1{2,}/', $letters)) {
                return true;
            }
            if (mb_strlen($letters) >= 5 && !preg_match('/[aeiouy]/', $letters)) {
                return true;
            }
            // Six consonants in a row; allows names like "Christ", "Schwartz" or "Kristhel".
            if (preg_match('/[^aeiouy]{6,}/', $letters)) {
                return true;
            }
            foreach (['qwertyuiop', 'asdfghjkl', 'zxcvbnm'] as $row) {
                for ($i = 0; $i + 5 <= strlen($row); $i++) {
                    $run = substr($row, $i, 5);
                    if (str_contains($letters, $run) || str_contains($letters, strrev($run))) {
                        return true;
                    }
                }
            }
        }

        // More than one capital inside a word (allows "McDonald" but not "aBcDeF").
        foreach (preg_split('/[\s.\'\-]+/u', $value, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (preg_match_all('/\p{Ll}\p{Lu}/u', $word) > 1) {
                return true;
            }
        }

        return false;
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
        $needle = 'purok ' . ltrim(preg_replace('/\D/', '', $input), '0');

        return $puroks->first(fn (Purok $purok) => mb_strtolower(preg_replace('/\s+/', ' ', trim($purok->name))) === $needle);
    }

    private function findSubgroup(Purok $purok, string $input): ?PurokSubgroup
    {
        $needle = $this->subgroupKey($input);
        $purokNumber = preg_replace('/\D/', '', $purok->name);

        // A bare letter ("c") means that letter under the row's own purok.
        if (preg_match('/^[a-z]$/', $needle)) {
            $needle = ltrim($purokNumber, '0') . $needle;
        }

        return $purok->subgroups->first(fn (PurokSubgroup $subgroup) => $this->subgroupKey($subgroup->name) === $needle);
    }

    // Reduces "3C", "3-c", "Purok 3 C" or "Sub-purok 3c" to a comparable "3c".
    private function subgroupKey(string $value): string
    {
        $value = mb_strtolower(trim($value));

        if (preg_match('/(\d+)\s*[-._ ]?\s*([a-z])\b/', $value, $m)) {
            return ltrim($m[1], '0') . $m[2];
        }

        return preg_replace('/[^a-z0-9]/', '', $value);
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
