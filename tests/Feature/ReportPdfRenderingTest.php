<?php

namespace Tests\Feature;

use App\Models\citizens;
use App\Services\ReportPdfDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReportPdfRenderingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function test_multi_page_citizen_pdf_renders_all_rows_without_browser_assets(): void
    {
        $this->app->instance('request', Request::create('/reports?export_format=pdf'));
        $citizens = collect();
        for ($i = 1; $i <= 125; $i++) {
            $citizen = new citizens([
                'Citizen_FName' => 'Peña '.$i,
                'Citizen_LName' => 'Test',
                'Citizen_BirthDate' => '1990-01-01',
                'Citizen_Sex' => 'Female',
                'Citizen_Purok' => 'Purok 1',
            ]);
            $citizen->id = $i;
            $citizen->setRelation('family', null)->setRelation('subgroup', null);
            $citizens->push($citizen);
        }

        $html = view('bhw.reports', [
            'availableSections' => ['citizens' => 'Citizen Profiles'],
            'selectedSections' => ['citizens'],
            'citizensReport' => $citizens,
            'reportFilterSummary' => 'Purok: Purok 1',
        ])->render();
        $this->assertStringNotContainsString('<form', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<link', $html);
        preg_match('/src="data:image\/png;base64,([^"]+)"/', $html, $logo);
        $this->assertSame(
            hash_file('sha256', public_path('images/barangay-amuyong.png')),
            hash('sha256', base64_decode($logo[1] ?? '', true))
        );
        $dom = new \DOMDocument;
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);
        $header = $xpath->query('//main[@id="printableReport"]//table[@class="report-letterhead"]');
        $this->assertSame(1, $header->length);
        $headerImages = $xpath->query('.//img', $header->item(0));
        $this->assertSame(2, $headerImages->length);
        $this->assertSame('Municipality of Alfonso logo', $headerImages->item(1)->getAttribute('alt'));
        $alfonsoBytes = base64_decode(explode(',', $headerImages->item(1)->getAttribute('src'), 2)[1], true);
        $this->assertSame(hash_file('sha256', public_path('images/alfonso.png')), hash('sha256', $alfonsoBytes));
        $this->assertStringContainsString('BARANGAY AMUYONG', $header->item(0)->textContent);

        $document = app(ReportPdfDocument::class)->prepare($html);
        $this->assertSame('landscape', $document['orientation']);
        $pdf = Pdf::loadHTML($document['html'])
            ->setOption('isFontSubsettingEnabled', true)
            ->setPaper('a4', $document['orientation']);
        $this->assertSame(5, $pdf->getDomPDF()->getDom()->getElementsByTagName('table')->length);
        $this->assertStringContainsString('Peña 125', $pdf->getDomPDF()->getDom()->textContent);
        $positions = [];
        $pdf->getDomPDF()->setCallbacks([['event' => 'end_frame', 'f' => function ($frame) use (&$positions): void {
            if ($frame->get_node()->nodeName === 'img') {
                $positions[] = $frame->get_position();
            }
        }]]);
        $content = $pdf->output();

        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Width 1254', $content);
        $this->assertCount(2, $positions);
        $this->assertLessThan($positions[1]['x'], $positions[0]['x']);
        $this->assertGreaterThan($pdf->getDomPDF()->getCanvas()->get_width() / 2, $positions[1]['x']);
        $this->assertEqualsWithDelta($positions[0]['y'], $positions[1]['y'], 1);
        $this->assertGreaterThan(1, $pdf->getDomPDF()->getCanvas()->get_page_count());
    }

    public function test_citizen_records_pdf_embeds_the_current_logo(): void
    {
        $html = view('exports.citizens-pdf', [
            'citizens' => collect([new citizens(['Citizen_FName' => 'Maria'])]),
            'filterSummary' => [], 'selectedFields' => ['Citizen_FName'],
            'fieldDefinitions' => ['Citizen_FName' => 'First Name'],
        ])->render();
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringContainsString('Maria', $html);
        $content = Pdf::loadHTML($html)->setPaper('a4', 'landscape')->output();
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Width 1254', $content);
    }
}
