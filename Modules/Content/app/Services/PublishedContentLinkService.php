<?php

namespace Modules\Content\Services;

use Modules\Content\Models\PublishedContentLink;
use Modules\Ingestion\Models\OrganicPost;

class PublishedContentLinkService
{
    public function attachOrganicPost(OrganicPost $organicPost): void
    {
        PublishedContentLink::query()
            ->where('asset_id', $organicPost->asset_id)
            ->where('platform_post_id', $organicPost->platform_post_id)
            ->whereNull('organic_post_id')
            ->update(['organic_post_id' => $organicPost->id]);
    }
}
