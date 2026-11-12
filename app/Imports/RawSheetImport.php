<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RawSheetImport implements ToArray, WithLimit, WithMultipleSheets
{
    public function limit(): int
    {
        return 20;
    }

    public function sheets(): array
    {
        return [0 => $this];
    }

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
