@php
    $faviconUrl = route('site.favicon');
    $faviconVersion = \App\Models\SystemSetting::logoVersion();
    $faviconUrl .= '?v=' . $faviconVersion;
@endphp
<link rel="icon" type="image/png" sizes="any" href="{{ $faviconUrl }}">
<link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
