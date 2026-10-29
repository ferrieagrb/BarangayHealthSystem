<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

class PublicAssetController extends Controller
{
    public function logo()
    {
        $path = SystemSetting::value('site_logo');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->file(public_path('images/amuyong.png'));
        }

        $contents = Storage::disk('public')->get($path);
        $mimeType = $this->imageMime($path);

        return response($contents, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

    private function imageMime(string $path): string
    {
        $byExtension = [
            'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
        ];

        return $byExtension[strtolower(pathinfo($path, PATHINFO_EXTENSION))]
            ?? (Storage::disk('public')->mimeType($path) ?: 'image/png');
    }

    public function announcementImage(string $path)
    {
        abort_if(str_contains($path, '..') || !Storage::disk('public')->exists($path), 404);

        $contents = Storage::disk('public')->get($path);
        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';

        return response($contents, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
