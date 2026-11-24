<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Referral Form</title>
    <style>
        @page { margin: 12mm 13mm; }
        body { margin: 0; padding: 0; }
        {!! file_get_contents(public_path('css/bhw/referral_document.css')) !!}
        .referral-document { font-family: DejaVu Sans, sans-serif; border-top: 6px solid #086347; padding-top: 12px; }
        .referral-document table { width: 100%; margin: 0; }
        .referral-document .document-center-name { font-size: 17px; }
        .referral-document table { border-spacing: 3px; }
        .referral-document .document-header, .referral-document .document-priority { border-spacing: 0; }
        .referral-document .document-field { height: 26px; padding: 5px 7px; }
        .referral-document .document-writing { height: 41px; }
        .referral-document .document-vitals .document-field { height: 25px; }
        .referral-document .document-value { min-height: 0; }
        .referral-document .document-section { font-size: 11px; line-height: 1.1; padding: 4px 10px; margin: 4px 0 1px; }
    </style>
</head>
<body>
    @include('bhw.referrals.partials.document', ['forPdf' => true])
</body>
</html>
