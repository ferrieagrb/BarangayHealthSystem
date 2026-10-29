<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

class PublicAssetController extends Controller
{
    public function favicon()
    {
        $path = SystemSetting::value('site_logo');
        $source = ($path && Storage::disk('public')->exists($path))
            ? Storage::disk('public')->get($path)
            : file_get_contents(public_path('images/amuyong.png'));

        $image = function_exists('imagecreatefromstring') ? @imagecreatefromstring($source) : false;
        if (!$image) {
            return $this->logo();
        }

        // Re-encode as a small PNG that keeps transparency, so the tab icon has no white box.
        $size = 64;
        $icon = imagecreatetruecolor($size, $size);
        imagealphablending($icon, false);
        imagesavealpha($icon, true);
        imagefill($icon, 0, 0, imagecolorallocatealpha($icon, 0, 0, 0, 127));
        imagecopyresampled($icon, $image, 0, 0, 0, 0, $size, $size, imagesx($image), imagesy($image));

        ob_start();
        imagepng($icon);
        $png = ob_get_clean();

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-cache, must-revalidate',
        ]);
    }

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
