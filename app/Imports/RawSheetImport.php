<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Events\BeforeImport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class RawSheetImport implements ToArray, WithLimit, WithColumnLimit, WithEvents
{
    private string $endColumn = 'A';

    public function array(array $array)
    {
    }

    public function limit(): int
    {
        return CitizensImport::HEADING_SEARCH_ROWS;
    }

    public function endColumn(): string
    {
        return $this->endColumn;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event): void {
                $highestColumn = 1;
                foreach ($event->getReader()->getDelegate()->getAllSheets() as $sheet) {
                    $highestColumn = max($highestColumn, Coordinate::columnIndexFromString($sheet->getHighestDataColumn()));
                }
                $this->endColumn = Coordinate::stringFromColumnIndex($highestColumn);
            },
        ];
    }
}
