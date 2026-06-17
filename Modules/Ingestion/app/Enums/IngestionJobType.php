<?php

namespace Modules\Ingestion\Enums;

enum IngestionJobType: string
{
    case OrganicFacebook = 'organic_facebook';
    case OrganicInstagram = 'organic_instagram';
    case StoriesWatcher = 'stories_watcher';
    case PaidMeta = 'paid_meta';
    case PaidGoogle = 'paid_google';
    case OrganicTikTok = 'organic_tiktok';
    case OrganicLinkedIn = 'organic_linkedin';
    case OrganicYouTube = 'organic_youtube';
}
