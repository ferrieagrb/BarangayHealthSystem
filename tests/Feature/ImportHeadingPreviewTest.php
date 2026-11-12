<?php

namespace Tests\Feature;

use App\Imports\CitizensImport;
use App\Imports\RawSheetImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ImportHeadingPreviewTest extends TestCase
{
    public function test_preview_reads_only_first_twenty_rows_of_first_sheet(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'heading-preview-');
        $book = new Spreadsheet();
        $book->getActiveSheet()->setCellValue('A1', 'Citizen list');
        $book->getActiveSheet()->fromArray(['First Name', 'Last Name'], null, 'A20');
        $book->getActiveSheet()->setCellValue('A1000', 'Outside preview');
        $book->createSheet()->setCellValue('A1', 'Other sheet');

        try {
            (new Xlsx($book))->save($path);
            $sheets = Excel::toArray(new RawSheetImport(), $path, null, \Maatwebsite\Excel\Excel::XLSX);

            $this->assertCount(1, $sheets);
            $this->assertCount(20, $sheets[0]);
            $this->assertSame(20, CitizensImport::detectHeadingRow($sheets[0]));
        } finally {
            $book->disconnectWorksheets();
            unlink($path);
        }
    }
}
