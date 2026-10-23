<?php

namespace App\Exports;

use App\Models\citizens;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CitizensExport implements FromCollection, WithHeadings, WithMapping, WithCustomStartCell, WithEvents
{
    public function __construct(
        private $citizenQuery = null,
        private array $filterSummary = [],
        private array $selectedFields = [],
        private array $fieldDefinitions = []
    )
    {
    }

    public function collection()
    {
        return ($this->citizenQuery ?: citizens::query()->orderBy('id'))->get();
    }

    public function headings(): array
    {
        return array_map(fn ($field) => $this->fieldDefinitions[$field] ?? $field, $this->selectedFields);
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A1', 'Citizen Records Export');
                $sheet->setCellValue('A2', 'Filters applied: ' . ($this->filterSummary ? implode(' | ', $this->filterSummary) : 'None'));
                $sheet->setCellValue('A3', 'Records shown: ' . $this->collection()->count());
                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
                $lastColumn = $this->columnLetter(count($this->selectedFields));
                $sheet->mergeCells('A1:' . $lastColumn . '1');
                $sheet->mergeCells('A2:' . $lastColumn . '2');
                $sheet->mergeCells('A3:' . $lastColumn . '3');
            },
        ];
    }

    public function map($citizen): array
    {
        return array_map(function ($field) use ($citizen) {
            if (in_array($field, ['Citizen_Pregnant', 'Citizen_InPills', 'Citizen_IsDeceased'], true)) {
                return $citizen->{$field} ? 'Yes' : 'No';
            }

            return (string) ($citizen->{$field} ?? '');
        }, $this->selectedFields);
    }

    private function columnLetter(int $column): string
    {
        $letter = '';
        while ($column > 0) {
            $column--;
            $letter = chr(65 + ($column % 26)) . $letter;
            $column = intdiv($column, 26);
        }

        return $letter ?: 'A';
    }
}
