<?php

namespace Modules\Ingestion\Services;

use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use RuntimeException;

class InstagramAccessTokenResolver
{
    public function resolve(ConnectedAsset $asset): string
    {
        if ($asset->asset_type !== AssetType::InstagramAccount) {
            throw new \InvalidArgumentException('Asset is not an Instagram account.');
        }

        $linkedPageId = $asset->metadata['linked_page_id'] ?? null;

        if (! is_string($linkedPageId) || $linkedPageId === '') {
            throw new RuntimeException('Missing linked Facebook page for Instagram asset.');
        }

        $pageAsset = ConnectedAsset::query()
            ->where('connection_id', $asset->connection_id)
            ->where('asset_type', AssetType::FacebookPage)
            ->where('platform_asset_id', $linkedPageId)
            ->where('is_active', true)
            ->first();

        $pageAccessToken = $pageAsset?->metadata['page_access_token'] ?? null;

        if (! is_string($pageAccessToken) || $pageAccessToken === '') {
            throw new RuntimeException('Missing page access token for linked Facebook page.');
        }

        return $pageAccessToken;
    }
}
