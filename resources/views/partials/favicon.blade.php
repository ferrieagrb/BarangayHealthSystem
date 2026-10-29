@php
    $faviconUrl = route('site.favicon');
    $faviconVersion = substr(md5((string) \App\Models\SystemSetting::value('site_logo', 'images/amuyong.png')), 0, 8);
    $faviconUrl .= '?v=' . $faviconVersion;
@endphp
<link rel="icon" type="image/png" sizes="any" href="{{ $faviconUrl }}">
<link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
