<?php

return [
    'meta' => [
        'app_id' => env('META_APP_ID'),
        'app_secret' => env('META_APP_SECRET'),
        'api_version' => env('META_API_VERSION', 'v22.0'),
        'redirect_uri' => env('META_REDIRECT_URI'),
        'scopes' => array_filter(explode(',', env(
            'META_OAUTH_SCOPES',
            'public_profile,email,pages_show_list,pages_read_engagement,business_management,ads_read,instagram_basic,instagram_manage_insights',
        ))),
    ],

    'google' => [
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
        'scopes' => [
            'https://www.googleapis.com/auth/adwords',
        ],
    ],
];
