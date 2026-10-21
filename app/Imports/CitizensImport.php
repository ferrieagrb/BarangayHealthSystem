<?php

namespace App\Imports;

use App\Models\citizens;
use App\Models\Purok;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CitizensImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public int $createdAccounts = 0;

    public function model(array $row)
    {
        // Validate each spreadsheet row.
        $validator = Validator::make($row, [
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'middle_name'     => 'required|string|max:255',
            'birthdate'       => 'required',
            'sex'             => 'required|string|in:Male,Female',
            'purok'           => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \RuntimeException(
                'Invalid citizen row: ' .
                $validator->errors()->first()
            );
        }

        // Convert Excel date values or readable date strings.
        $birthDateValue = $row['birthdate'];

        if (is_numeric($birthDateValue)) {
            $birthDate = Carbon::instance(
                ExcelDate::excelToDateTimeObject($birthDateValue)
            )->toDateString();
        } else {
            $birthDate = Carbon::parse($birthDateValue)->toDateString();
        }

        // Reject future birth dates.
        if (Carbon::parse($birthDate)->isFuture()) {
            throw new \RuntimeException('Birth date cannot be in the future.');
        }

        $purokName = trim($row['purok']);
        $purok = Purok::with('subgroups')->where('name', $purokName)->first();

        if (!$purok) {
            throw new \RuntimeException("Purok '{$purokName}' does not exist.");
        }

        $subgroup = $purok->subgroups->first();

        return DB::transaction(function () use ($row, $birthDate, $purokName, $subgroup) {
            $firstName = trim($row['first_name']);
            $middleName = trim($row['middle_name']);
            $lastName = trim($row['last_name']);

            $citizen = citizens::create([
                'Citizen_FName' => $firstName,
                'Citizen_MName' => $middleName,
                'Citizen_LName' => $lastName,
                'Citizen_BirthDate' => $birthDate,
                'Citizen_Age' => Carbon::parse($birthDate)->age,
                'Citizen_ContactNo' => '',
                'Citizen_Sex' => trim($row['sex']),
                'Citizen_Purok' => $purokName,
                'subgroup_id' => $subgroup?->id,
                'qr_token' => (string) Str::uuid(),
            ]);

            $emailSlug = Str::slug(trim($firstName . ' ' . $lastName)) ?: 'citizen';
            $email = $emailSlug . '.' . $citizen->id . '@amuyong.local';

            User::create([
                'name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
                'email' => $email,
                'password' => Hash::make('Barangay123!'),
                'role' => 'citizen',
                'citizen_id' => $citizen->id,
                'must_change_password' => true,
            ]);

            $this->createdAccounts++;

            return $citizen;
        });
    }
}
