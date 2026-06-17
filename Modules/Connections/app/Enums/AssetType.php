<?php

namespace Modules\Connections\Enums;

enum AssetType: string
{
    case FacebookPage = 'fb_page';
    case InstagramAccount = 'ig_account';
    case MetaAds = 'meta_ads';
    case GoogleAds = 'google_ads';
    case TikTokAccount = 'tiktok_account';
    case LinkedInPage = 'linkedin_page';
    case YouTubeChannel = 'youtube_channel';

    public function isPaid(): bool
    {
        return match ($this) {
            self::MetaAds, self::GoogleAds => true,
            default => false,
        };
    }
}
