<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustomReportExport implements WithMultipleSheets
{
    public function __construct(
        private string $reportTitle,
        private string $generatedAt,
        private string $filterSummary,
        private array $sections
    ) {}

    public function sheets(): array
    {
        return array_map(
            fn (array $section) => new CustomReportSheet(
                $this->reportTitle,
                $this->generatedAt,
                $this->filterSummary,
                $section
            ),
            $this->sections
        );
    }
}
