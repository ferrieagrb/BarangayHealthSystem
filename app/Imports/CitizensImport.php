<?php

namespace App\Imports;

use App\Models\citizens;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CitizensImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Validate each spreadsheet row.
        $validator = Validator::make($row, [
            'citizen_fname'     => 'required|string|max:255',
            'citizen_lname'     => 'required|string|max:255',
            'citizen_birthdate' => 'required',
            'citizen_contactno' => 'required|string|max:50',
            'citizen_purok'     => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \RuntimeException(
                'Invalid citizen row: ' .
                $validator->errors()->first()
            );
        }

        // Convert Excel date values or readable date strings.
        $birthDateValue = $row['citizen_birthdate'];

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

        // Save the citizen using your existing database columns.
        return new citizens([
            'Citizen_FName'     => trim($row['citizen_fname']),
            'Citizen_LName'     => trim($row['citizen_lname']),
            'Citizen_BirthDate' => $birthDate,
            'Citizen_ContactNo' => trim((string) $row['citizen_contactno']),
            'Citizen_Purok'     => trim($row['citizen_purok']),
            'Citizen_Age'       => Carbon::parse($birthDate)->age,
        ]);
    }
}
