<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'access_token' => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY', 'AIzaSyBBb3WrQ40r3wzE1NKWVQFYtock7GASNJs'),
    ],

    'rxnorm' => [
        'approximate_url' => env('RXNORM_APPROXIMATE_URL', 'https://rxnav.nlm.nih.gov/REST/approximateTerm.json'),
    ],

    'cdc_cvx' => [
        'url' => env('CDC_CVX_URL', 'https://www2a.cdc.gov/vaccines/iis/iisstandards/XML2.asp?rpt=cvx'),
    ],

    'who_icd' => [
        'client_id' => env('WHO_ICD_CLIENT_ID'),
        'client_secret' => env('WHO_ICD_CLIENT_SECRET'),
        'token_url' => env('WHO_ICD_TOKEN_URL', 'https://icdaccessmanagement.who.int/connect/token'),
        'api_url' => env('WHO_ICD_API_URL', 'https://id.who.int'),
        'release' => env('WHO_ICD_RELEASE', '2026-01'),
    ],

];
