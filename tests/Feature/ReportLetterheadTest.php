<?php

namespace Tests\Feature;

use App\Exports\CustomReportExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportLetterheadTest extends TestCase
{
    public function test_both_logos_survive_excel_export_on_narrow_wide_and_household_sheets(): void
    {
        $headings = array_map(fn ($index) => 'Column '.$index, range(1, 30));
        $export = new CustomReportExport('Test report', 'Nov 19, 2026', '', [
            ['title' => 'Narrow report', 'headings' => ['Purok', 'Count'], 'rows' => [['Purok 1', 3]]],
            ['title' => 'Wide report', 'headings' => $headings, 'rows' => [range(1, 30)]],
            ['title' => 'Households', 'type' => 'households', 'groups' => []],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'report-letterhead-');
        file_put_contents($path, Excel::raw($export, ExcelFormat::XLSX));
        try {
            $workbook = IOFactory::load($path);
            foreach (['D', 'AD', 'J'] as $index => $lastColumn) {
                $sheet = $workbook->getSheet($index);
                $this->assertSame('BARANGAY AMUYONG', $sheet->getCell('A1')->getValue());
                $this->assertSame('center', $sheet->getStyle('A1')->getAlignment()->getHorizontal());
                $this->assertContains('A1:'.$lastColumn.'1', $sheet->getMergeCells());
                $this->assertCount(2, $sheet->getDrawingCollection());
                [$left, $right] = iterator_to_array($sheet->getDrawingCollection());
                $this->assertSame('A1', $left->getCoordinates());
                $this->assertSame($lastColumn.'1', $right->getCoordinates());
                $this->assertGreaterThanOrEqual(0, $right->getOffsetX());
                $this->assertSame(
                    hash_file('sha256', public_path('images/barangay-amuyong.png')),
                    hash_file('sha256', $left->getPath())
                );
                $this->assertSame(
                    hash_file('sha256', public_path('images/alfonso.png')),
                    hash_file('sha256', $right->getPath())
                );
                $this->assertSame('A1:'.$lastColumn.'8', $sheet->getPageSetup()->getPrintArea());
            }
            $this->assertSame('Count', $workbook->getSheet(0)->getCell('B7')->getValue());
            $this->assertEquals(3, $workbook->getSheet(0)->getCell('B8')->getValue());
            $this->assertEquals(30, $workbook->getSheet(1)->getCell('AD8')->getValue());
            $this->assertSame('Family ID', $workbook->getSheet(2)->getCell('A7')->getValue());
        } finally {
            unlink($path);
        }
    }
}
