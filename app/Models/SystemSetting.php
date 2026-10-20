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

    public static function logoUrl(): string
    {
        $path = static::value('site_logo', 'images/amuyong.png');

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        $storagePath = '/storage/' . ltrim($path, '/');

        return app()->runningInConsole()
            ? Storage::disk('public')->url($path)
            : request()->getSchemeAndHttpHost() . $storagePath;
    }
}
