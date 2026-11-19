<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Drawing as DrawingUnits;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportLetterhead
{
    public static function drawings(int $columnCount): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex(max(4, $columnCount));
        $amuyong = new Drawing;
        $amuyong->setName('Barangay Amuyong Logo');
        $amuyong->setDescription('Barangay Amuyong BHEAMS logo');
        $amuyong->setPath(public_path('images/barangay-amuyong.png'));
        $amuyong->setCoordinates('A1');
        $amuyong->setHeight(64);
        $amuyong->setOffsetX(8);
        $amuyong->setOffsetY(8);

        $alfonso = new Drawing;
        $alfonso->setName('Municipality of Alfonso Logo');
        $alfonso->setDescription('Municipality of Alfonso, Cavite logo');
        $alfonso->setPath(public_path('images/alfonso.png'));
        $alfonso->setCoordinates($lastColumn.'1');
        $alfonso->setHeight(64);
        $alfonso->setOffsetY(8);

        return [$amuyong, $alfonso];
    }

    public static function format(Worksheet $sheet, int $columnCount): void
    {
        $headerColumns = max(4, $columnCount);
        $lastColumn = Coordinate::stringFromColumnIndex($headerColumns);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->mergeCells("A2:{$lastColumn}2");
        $sheet->setCellValue('A1', 'BARANGAY AMUYONG');
        $sheet->setCellValue('A2', 'Municipality of Alfonso | Province of Cavite');
        $sheet->getStyle("A1:{$lastColumn}2")->applyFromArray([
            'font' => ['name' => 'Arial', 'color' => ['rgb' => '07573E']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A2')->getFont()->setSize(10);
        $sheet->getRowDimension(1)->setRowHeight(36);
        $sheet->getRowDimension(2)->setRowHeight(30);

        foreach (['A', $lastColumn] as $column) {
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setWidth(max(14, $dimension->getWidth()));
        }
        if ($headerColumns === 4) {
            foreach (['B', 'C'] as $column) {
                $dimension = $sheet->getColumnDimension($column);
                $dimension->setWidth(max(20, $dimension->getWidth()));
            }
        }

        // Position the right logo after data column widths have been calculated.
        $width = DrawingUnits::cellDimensionToPixels(
            $sheet->getColumnDimension($lastColumn)->getWidth(),
            $sheet->getParent()->getDefaultStyle()->getFont()
        );
        foreach ($sheet->getDrawingCollection() as $drawing) {
            if ($drawing->getName() === 'Municipality of Alfonso Logo') {
                $drawing->setOffsetX(max(0, $width - $drawing->getWidth() - 8));
            }
        }
    }
}
