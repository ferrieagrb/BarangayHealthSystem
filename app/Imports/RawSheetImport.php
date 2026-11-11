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
}
