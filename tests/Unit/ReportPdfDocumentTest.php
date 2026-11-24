<?php

namespace Tests\Unit;

use App\Services\ReportPdfDocument;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

class ReportPdfDocumentTest extends TestCase
{
    public function test_long_tables_preserve_every_row_and_repeat_headings(): void
    {
        $rows = '';
        for ($i = 1; $i <= 125; $i++) {
            $rows .= '<tr><td>Peña '.$i.'</td><td>'.$i.'</td></tr>';
        }
        $html = '<html><head><meta charset="UTF-8"></head><body><table class="report-table"><thead><tr><th>Name</th><th>Count</th></tr></thead><tbody>'.$rows.'</tbody><tfoot><tr><td>Total</td><td>125</td></tr></tfoot></table></body></html>';

        $result = (new ReportPdfDocument)->prepare($html);
        $xpath = $this->xpath($result['html']);
        $this->assertSame('portrait', $result['orientation']);
        $this->assertSame(4, $xpath->query('//table')->length);
        $this->assertSame(4, $xpath->query('//table/thead')->length);
        $this->assertSame(125, $xpath->query('//table/tbody/tr')->length);
        $this->assertSame(1, $xpath->query('//table/tfoot')->length);
        $this->assertSame(4, $xpath->query('//table[@class="report-table"]')->length);
        $this->assertSame('Peña 125', $xpath->query('(//tbody/tr)[last()]/td[1]')->item(0)->textContent);
        $this->assertStringNotContainsString('<?xml', $result['html']);
        $this->assertSame(range(1, 125), array_map(
            fn ($cell) => (int) $cell->textContent,
            iterator_to_array($xpath->query('//tbody/tr/td[2]'))
        ));
    }

    public function test_household_rowspans_stay_together_at_chunk_boundaries(): void
    {
        $rows = str_repeat('<tr><td>Single member</td></tr>', 39);
        $rows .= '<tr><td rowspan="3">Family</td><td>Head</td></tr><tr><td>Member 2</td></tr><tr><td>Member 3</td></tr>';
        $rows .= str_repeat('<tr><td>Next family</td></tr>', 5);
        $result = (new ReportPdfDocument)->prepare('<html><head></head><body><table><thead><tr><th colspan="10">Households</th></tr></thead><tbody>'.$rows.'</tbody></table></body></html>');
        $xpath = $this->xpath($result['html']);

        $this->assertSame('landscape', $result['orientation']);
        $this->assertSame(42, $xpath->query('(//table)[1]/tbody/tr')->length);
        $this->assertSame(5, $xpath->query('(//table)[2]/tbody/tr')->length);
        $this->assertStringContainsString('@page { size: A4 landscape; }', $result['html']);
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $document->loadHTML($html);

        return new DOMXPath($document);
    }
}
