<?php

namespace Modules\Connections\Enums;

enum AssetType: string
{
    case FacebookPage = 'fb_page';
    case InstagramAccount = 'ig_account';
    case MetaAds = 'meta_ads';
    case GoogleAds = 'google_ads';
}
