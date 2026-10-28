<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class CustomReportSheet extends DefaultValueBinder implements FromArray, WithColumnFormatting, WithCustomValueBinder, WithDrawings, WithEvents, WithStrictNullComparison, WithTitle
{
    public function __construct(
        private string $reportTitle,
        private string $generatedAt,
        private string $filterSummary,
        private array $section
    ) {}

    public function array(): array
    {
        $rows = [
            ['', 'BARANGAY HEALTH MANAGEMENT SYSTEM'],
            ['', 'BARANGAY AMUYONG | BHEAMS'],
            ['Barangay Health Management System - '.$this->reportTitle.' Report'],
            ['Generated: '.$this->generatedAt],
            ['Filters: '.($this->filterSummary ?: 'None')],
            array_fill(0, count($this->section['headings']), null),
            $this->section['headings'],
        ];

        foreach ($this->section['rows'] as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function columnFormats(): array
    {
        return $this->section['column_formats'] ?? [];
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
        if (is_numeric($value) && array_key_exists($cell->getColumn(), $this->section['column_formats'] ?? [])) {
            $cell->setValueExplicit($value + 0, DataType::TYPE_NUMERIC);

            return true;
        }

        if (is_string($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        }

        if (is_int($value) || is_float($value)) {
            $cell->setValueExplicit($value, DataType::TYPE_NUMERIC);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function title(): string
    {
        return mb_substr(preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $this->section['title']), 0, 31);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $columnCount = count($this->section['headings']);
                $lastColumn = $this->columnLetter($columnCount);
                $headerLastColumn = $columnCount > 1 ? $lastColumn : 'B';
                $lastRow = max(7, 7 + count($this->section['rows']));

                $sheet->mergeCells("B1:{$headerLastColumn}1");
                $sheet->mergeCells("B2:{$headerLastColumn}2");
                foreach (range(3, 5) as $row) {
                    $sheet->mergeCells("A{$row}:{$headerLastColumn}{$row}");
                }

                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '173B82']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A1')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '173B82']],
                ]);
                $sheet->getStyle("B2:{$headerLastColumn}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '173B82']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle("A3:{$headerLastColumn}3")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '173B82']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getStyle("A4:{$headerLastColumn}5")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '475569']],
                ]);
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getFont()->setName('Arial');
                $sheet->getStyle("A7:{$lastColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
                ]);

                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$lastColumn}{$lastRow}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                    ]);

                    for ($row = 8; $row <= $lastRow; $row += 2) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F8FAFC');
                    }

                    $sheet->setAutoFilter("A7:{$lastColumn}{$lastRow}");
                } else {
                    $sheet->setAutoFilter("A7:{$lastColumn}7");
                }

                $columnWidths = [];
                for ($column = 1; $column <= $columnCount; $column++) {
                    $letter = $this->columnLetter($column);
                    $longestValue = mb_strlen((string) $this->section['headings'][$column - 1]);
                    foreach ($this->section['rows'] as $row) {
                        $value = $row[$column - 1] ?? '';
                        if (is_scalar($value)) {
                            $longestValue = max($longestValue, mb_strlen((string) $value));
                        }
                    }
                    $columnWidths[$column - 1] = min(45, max(12, $longestValue + 2));
                    $sheet->getColumnDimension($letter)->setWidth($columnWidths[$column - 1]);
                    if (isset(($this->section['column_formats'] ?? [])[$letter]) && $lastRow > 7) {
                        $sheet->getStyle("{$letter}8:{$letter}{$lastRow}")
                            ->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }
                }

                $excelRow = 8;
                foreach ($this->section['rows'] as $row) {
                    $lineCount = 1;
                    foreach ($row as $column => $value) {
                        if (! is_scalar($value)) {
                            continue;
                        }
                        $width = max(1, ($columnWidths[$column] ?? 12) - 2);
                        $lineCount = max($lineCount, (int) ceil(mb_strlen((string) $value) / $width));
                    }
                    $sheet->getRowDimension($excelRow++)->setRowHeight(min(108, max(22, ($lineCount * 15) + 6)));
                }

                $sheet->getColumnDimension('A')->setWidth(max(14, $sheet->getColumnDimension('A')->getWidth()));
                $sheet->getDefaultRowDimension()->setRowHeight(22);
                $sheet->getRowDimension(1)->setRowHeight(38);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(32);
                $sheet->getRowDimension(7)->setRowHeight(34);
                $sheet->freezePane('A8');
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setRowsToRepeatAtTopByStartAndEnd(7, 7);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageMargins()
                    ->setTop(0.35)
                    ->setRight(0.3)
                    ->setBottom(0.45)
                    ->setLeft(0.3)
                    ->setHeader(0.15)
                    ->setFooter(0.2);
                $sheet->getPageSetup()->setPrintArea("A1:{$headerLastColumn}{$lastRow}");
            },
        ];
    }

    private function columnLetter(int $column): string
    {
        $letter = '';
        while ($column > 0) {
            $column--;
            $letter = chr(65 + ($column % 26)).$letter;
            $column = intdiv($column, 26);
        }

        return $letter;
    }
}
