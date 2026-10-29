@php
    $faviconUrl = \App\Models\SystemSetting::logoUrl();
    $faviconVersion = substr(md5((string) \App\Models\SystemSetting::value('site_logo', 'images/amuyong.png')), 0, 8);
    $faviconUrl .= (str_contains($faviconUrl, '?') ? '&' : '?') . 'v=' . $faviconVersion;
@endphp
<link rel="icon" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
