<?php

namespace App\Exports;

use App\Support\FamilyCode;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Household list grouped like the preview/PDF: Purok > Subgroup > families with their members.
 */
class HouseholdProfilesSheet extends DefaultValueBinder implements FromArray, WithCustomValueBinder, WithDrawings, WithEvents, WithStrictNullComparison, WithTitle
{
    private const HEADINGS = ['Family ID', 'Family Name', 'Family Head', 'Member', 'Role', 'Age', 'Sex', 'Birthdate', 'Contact No.', 'Purok / Subgroup'];

    private ?array $built = null;

    public function __construct(
        private string $reportTitle,
        private string $generatedAt,
        private string $filterSummary,
        private array $section
    ) {}

    public function array(): array
    {
        return $this->build()['rows'];
    }

    public function title(): string
    {
        return mb_substr($this->section['title'], 0, 31);
    }

    public function drawings(): array
    {
        $drawing = new Drawing;
        $drawing->setName('BHEAMS Logo');
        $drawing->setDescription('Barangay Amuyong BHEAMS logo');
        $drawing->setPath(public_path('images/amuyong.png'));
        $drawing->setCoordinates('A1');
        $drawing->setHeight(42);
        $drawing->setOffsetX(6);
        $drawing->setOffsetY(5);

        return [$drawing];
    }

    public function bindValue(Cell $cell, mixed $value): bool
    {
        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $built = $this->build();
                $last = Coordinate::stringFromColumnIndex(count(self::HEADINGS));
                $lastRow = count($built['rows']);

                $sheet->mergeCells("B1:{$last}1");
                $sheet->mergeCells("B2:{$last}2");
                foreach ([3, 4, 5] as $row) {
                    $sheet->mergeCells("A{$row}:{$last}{$row}");
                }

                $sheet->getStyle("A1:{$last}{$lastRow}")->getFont()->setName('Arial');
                $sheet->getStyle("A1:{$last}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '173B82']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("B2:{$last}2")->applyFromArray(['font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '173B82']]]);
                $sheet->getStyle("A3:{$last}3")->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '173B82']]]);
                $sheet->getStyle("A4:{$last}5")->applyFromArray(['font' => ['size' => 10, 'color' => ['rgb' => '475569']]]);
                $sheet->getRowDimension(1)->setRowHeight(34);

                $sheet->getStyle("A7:{$last}7")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                foreach ([14, 24, 20, 30, 12, 9, 10, 16, 16, 26] as $index => $width) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($index + 1))->setWidth($width);
                }

                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$last}{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);
                }

                foreach ($built['ops'] as $op) {
                    if ($op['type'] === 'merge') {
                        $sheet->mergeCells($op['range']);
                    } else {
                        $sheet->getStyle($op['range'])->applyFromArray($op['style']);
                    }
                }

                $sheet->freezePane('A8');
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
                $sheet->getSheetView()->setZoomScale(100);
            },
        ];
    }

    private function build(): array
    {
        if ($this->built !== null) {
            return $this->built;
        }

        $columns = count(self::HEADINGS);
        $last = Coordinate::stringFromColumnIndex($columns);
        $pad = fn (array $row) => array_pad($row, $columns, null);
        $rows = [
            $pad(['', 'BARANGAY HEALTH MANAGEMENT SYSTEM']),
            $pad(['', 'BARANGAY AMUYONG | BHEAMS']),
            $pad(['Barangay Health Management System - '.$this->reportTitle.' Report']),
            $pad(['Generated: '.$this->generatedAt]),
            $pad(['Filters: '.($this->filterSummary ?: 'None')]),
            $pad([]),
            self::HEADINGS,
        ];
        $ops = [];

        foreach ($this->section['groups'] as $purok) {
            $row = count($rows) + 1;
            $rows[] = $pad([$purok['name'].' â€” Qty: '.$purok['count']]);
            $ops[] = ['type' => 'merge', 'range' => "A{$row}:{$last}{$row}"];
            $ops[] = ['type' => 'style', 'range' => "A{$row}:{$last}{$row}", 'style' => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '173B82']],
            ]];

            foreach ($purok['subgroups'] as $subgroup) {
                $label = FamilyCode::subgroupLabel($purok['name'], $subgroup['raw_name']);
                $row = count($rows) + 1;
                $rows[] = $pad([$label.' â€¢ Qty: '.$subgroup['count']]);
                $ops[] = ['type' => 'merge', 'range' => "A{$row}:{$last}{$row}"];
                $ops[] = ['type' => 'style', 'range' => "A{$row}:{$last}{$row}", 'style' => [
                    'font' => ['bold' => true, 'color' => ['rgb' => '173B82']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                ]];

                foreach ($subgroup['families'] as $family) {
                    $start = count($rows) + 1;
                    $members = $family['members'] ?: [null];

                    foreach ($members as $index => $member) {
                        $line = $start + $index;
                        $rows[] = [
                            $index === 0 ? $family['code'] : null,
                            $index === 0 ? $family['name'] : null,
                            $index === 0 ? $family['head'] : null,
                            $member['name'] ?? 'No members',
                            $member ? ($member['is_head'] ? 'Head' : 'Member') : '',
                            $member['age'] ?? '',
                            $member['sex'] ?? '',
                            $member['birthdate'] ?? '',
                            $member['contact'] ?? '',
                            $purok['name'].' / '.$label,
                        ];
                        if ($member && $member['is_head']) {
                            $ops[] = ['type' => 'style', 'range' => "D{$line}:I{$line}", 'style' => [
                                'font' => ['bold' => true, 'color' => ['rgb' => '173B82']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2FF']],
                            ]];
                        }
                    }

                    $end = $start + count($members) - 1;
                    if ($end > $start) {
                        foreach (['A', 'B', 'C'] as $column) {
                            $ops[] = ['type' => 'merge', 'range' => "{$column}{$start}:{$column}{$end}"];
                        }
                    }
                    $ops[] = ['type' => 'style', 'range' => "A{$start}:C{$end}", 'style' => [
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
                    ]];
                    $ops[] = ['type' => 'style', 'range' => "F{$start}:I{$end}", 'style' => [
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]];
                }
            }
        }

        if (! $this->section['groups']) {
            $rows[] = $pad(['No households match the selected filters.']);
        }

        return $this->built = ['rows' => $rows, 'ops' => $ops];
    }
}
