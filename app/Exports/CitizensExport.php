<?php

namespace App\Exports;

use App\Models\citizens;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CitizensExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return citizens::query()
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Citizen ID',
            'First Name',
            'Last Name',
            'Birth Date',
            'Contact Number',
            'Purok',
            'Family ID',
            'Age',
        ];
    }

    public function map($citizen): array
    {
        return [
            $citizen->id,
            $citizen->Citizen_FName,
            $citizen->Citizen_LName,
            $citizen->Citizen_BirthDate,
            (string) $citizen->Citizen_ContactNo,
            $citizen->Citizen_Purok,
            $citizen->family_id,
            $citizen->Citizen_Age,
        ];
    }
}
