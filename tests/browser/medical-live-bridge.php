<?php

// CLI-only bridge for offline-rendered forms. Never reads patient records.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['cache.default' => 'array']);
$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
echo json_encode(app(App\Services\MedicalTerminologySearch::class)->search(
    $input['type'], $input['q'], new App\Models\User(['role' => 'bhw']), $input['category'] ?? null
), JSON_THROW_ON_ERROR);
