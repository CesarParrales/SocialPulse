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
        'system_user_id' => env('META_SYSTEM_USER_ID'),
        'system_user_access_token' => env('META_SYSTEM_USER_ACCESS_TOKEN'),
        'business_id' => env('META_BUSINESS_ID'),
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

    'tiktok' => [
        'client_key' => env('TIKTOK_CLIENT_KEY'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'redirect_uri' => env('TIKTOK_REDIRECT_URI'),
        'scopes' => array_filter(explode(',', env(
            'TIKTOK_OAUTH_SCOPES',
            'user.info.basic,video.list',
        ))),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect_uri' => env('LINKEDIN_REDIRECT_URI'),
        'scopes' => array_filter(explode(',', env(
            'LINKEDIN_OAUTH_SCOPES',
            'r_organization_admin,r_organization_social',
        ))),
        'api_version' => env('LINKEDIN_API_VERSION', '202405'),
    ],

    'youtube' => [
        'client_id' => env('YOUTUBE_CLIENT_ID'),
        'client_secret' => env('YOUTUBE_CLIENT_SECRET'),
        'redirect_uri' => env('YOUTUBE_REDIRECT_URI'),
        'scopes' => array_filter(explode(',', env(
            'YOUTUBE_OAUTH_SCOPES',
            'https://www.googleapis.com/auth/youtube.readonly',
        ))),
    ],
];
