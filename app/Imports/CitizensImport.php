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
    public int $createdAccounts = 0;
    private string $hashedPassword;

    public function __construct()
    {
        $this->hashedPassword = Hash::make('Barangay123!', ['rounds' => 8]);
    }

    public function model(array $row)
    {
        $firstName  = trim($row['first_name'] ?? $row['First Name'] ?? $row['FIRST_NAME'] ?? '');
        $lastName   = trim($row['last_name'] ?? $row['Last Name'] ?? $row['LAST_NAME'] ?? '');
        $purokInput = trim($row['purok'] ?? $row['Purok'] ?? $row['PUROK'] ?? '');

        if ($firstName === '' || $lastName === '' || $purokInput === '' || strcasecmp($firstName, 'first_name') === 0) {
            return null;
        }

        $middleName     = trim($row['middle_name'] ?? $row['Middle Name'] ?? $row['MIDDLE_NAME'] ?? '');
        $birthDateValue = $row['birthdate'] ?? $row['Birthdate'] ?? $row['BIRTHDATE'] ?? null;
        $sexInput       = trim($row['sex'] ?? $row['Sex'] ?? $row['SEX'] ?? '');
        $subPurokInput  = trim($row['sub_purok'] ?? $row['subpurok'] ?? $row['Sub Purok'] ?? $row['SubPurok'] ?? $row['subgroup'] ?? '');

        // Handle birthdate and age naturally as null when blank
        $birthDate = null;
        $age       = null;
        if (!empty($birthDateValue)) {
            try {
                if (is_numeric($birthDateValue)) {
                    $birthDate = Carbon::instance(ExcelDate::excelToDateTimeObject($birthDateValue))->toDateString();
                } else {
                    $birthDate = Carbon::parse($birthDateValue)->toDateString();
                }
                $age = Carbon::parse($birthDate)->age;
            } catch (\Exception $e) {
                $birthDate = null;
                $age = null;
            }
        }

        // Flexible Purok lookup
        $purok = Purok::with('subgroups')
            ->where('name', 'like', $purokInput)
            ->orWhere('name', 'like', 'Purok ' . $purokInput)
            ->orWhere('name', 'like', '%' . $purokInput . '%')
            ->first();

        if (!$purok) {
            $purok = Purok::with('subgroups')->first();
        }

        $purokName = $purok ? $purok->name : $purokInput;

        // Lookup SubPurok / Subgroup by name
        $subgroup = null;
        if (!empty($subPurokInput)) {
            $subgroupQuery = PurokSubgroup::where('name', 'like', $subPurokInput)
                ->orWhere('name', 'like', '%' . $subPurokInput . '%');

            if ($purok) {
                // Prioritize subgroup belonging to the matched Purok
                $subgroup = (clone $subgroupQuery)->where('purok_id', $purok->id)->first();
            }

            if (!$subgroup) {
                $subgroup = $subgroupQuery->first();
            }
        }

        // Fallback to the first available subgroup of the purok if not specified
        if (!$subgroup && $purok) {
            $subgroup = $purok->subgroups->first();
        }

        return DB::transaction(function () use ($firstName, $lastName, $middleName, $birthDate, $age, $sexInput, $purokName, $subgroup) {
            $sex = null;
            if (!empty($sexInput)) {
                $formattedSex = ucfirst(strtolower($sexInput));
                if (in_array($formattedSex, ['Male', 'Female'])) {
                    $sex = $formattedSex;
                }
            }

            $citizen = citizens::create([
                'Citizen_FName'     => $firstName,
                'Citizen_MName'     => $middleName,
                'Citizen_LName'     => $lastName,
                'Citizen_BirthDate' => $birthDate,
                'Citizen_Age'       => $age,
                'Citizen_ContactNo' => '',
                'Citizen_Sex'       => $sex,
                'Citizen_Purok'     => $purokName,
                'subgroup_id'       => $subgroup?->id, // Linked SubPurok ID[cite: 4, 5]
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
}