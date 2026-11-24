<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function value(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function boolean(string $key, bool $default = false): bool
    {
        return filter_var(static::value($key, $default), FILTER_VALIDATE_BOOLEAN);
    }

    public static function passwordReentryRequired(): bool
    {
        return static::boolean('password_reentry_required', true);
    }

    public static function logoUrl(): string
    {
        $path = static::value('site_logo', 'images/barangay-amuyong.png');

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        return app()->runningInConsole()
            ? Storage::disk('public')->url($path)
            : route('site.logo');
    }

    public static function logoDataUri(): string
    {
        $path = static::value('site_logo', 'images/barangay-amuyong.png');
        $disk = Storage::disk('public');
        $contents = str_starts_with($path, 'images/') && is_file(public_path($path))
            ? file_get_contents(public_path($path))
            : ($disk->exists($path) ? $disk->get($path) : false);
        $image = $contents !== false ? @getimagesizefromstring($contents) : false;

        if ($image === false) {
            $contents = file_get_contents(public_path('images/barangay-amuyong.png'));
            $image = getimagesizefromstring($contents);
        }

        // Embed the current image bytes so PDFs do not reuse a URL or filesystem image cache.
        return 'data:'.$image['mime'].';base64,'.base64_encode($contents);
    }
}
