<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('medical:refresh-references', function () {
    $counts = app(\App\Services\MedicalTerminologySearch::class)->refreshOfficialReferences();
    $this->info('Refreshed official terminology: ' . json_encode($counts));
})->purpose('Refresh published WHO/DOH terminology without using inventory or patient records');
