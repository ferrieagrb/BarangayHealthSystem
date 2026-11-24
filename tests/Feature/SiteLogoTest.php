<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackPageViews;
use App\Models\SystemSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteLogoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(TrackPageViews::class);

        Schema::create('system_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function test_default_header_favicon_and_report_use_the_current_official_logo(): void
    {
        $contents = file_get_contents(public_path('images/barangay-amuyong.png'));
        $version = substr(hash('sha256', $contents), 0, 16);

        $this->assertStringEndsWith('/images/barangay-amuyong.png?v='.$version, SystemSetting::logoUrl());
        $this->assertStringContainsString(SystemSetting::logoUrl(), view('public.partials.header')->render());
        $this->assertStringContainsString('/site-favicon.png?v='.$version, view('partials.favicon')->render());
        $this->assertStringContainsString('data:image/png;base64,'.base64_encode($contents), view('bhw.partials.report-letterhead')->render());
    }

    public function test_replacing_an_upload_at_the_same_path_refreshes_logo_and_favicon_versions(): void
    {
        Storage::fake('public');
        $path = 'site-logo/current.png';
        SystemSetting::create(['key' => 'site_logo', 'value' => $path]);
        $original = UploadedFile::fake()->image('original.png', 4, 4);
        Storage::disk('public')->put($path, file_get_contents($original->getRealPath()));
        $previousUrl = SystemSetting::logoUrl();
        $previousFavicon = view('partials.favicon')->render();

        $replacement = UploadedFile::fake()->image('replacement.png', 8, 8);
        $contents = file_get_contents($replacement->getRealPath());
        Storage::disk('public')->put($path, $contents);
        $version = substr(hash('sha256', $contents), 0, 16);

        $this->assertNotSame($previousUrl, SystemSetting::logoUrl());
        $this->assertStringEndsWith('?v='.$version, SystemSetting::logoUrl());
        $this->assertNotSame($previousFavicon, view('partials.favicon')->render());
        $this->assertStringContainsString('/site-favicon.png?v='.$version, view('partials.favicon')->render());
        $this->assertStringContainsString('data:image/png;base64,'.base64_encode($contents), view('bhw.partials.report-letterhead')->render());
        $this->get(route('site.logo'))->assertOk()->assertContent($contents);
    }
}
