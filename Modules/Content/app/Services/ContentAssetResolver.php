<?php

namespace Modules\Content\Services;

use Illuminate\Support\Collection;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Enums\ContentChannel;
use Modules\Workspaces\Models\Workspace;

class ContentAssetResolver
{
    /**
     * @return Collection<int, ConnectedAsset>
     */
    public function assetsForChannel(Workspace $workspace, ContentChannel $channel): Collection
    {
        $assetType = match ($channel) {
            ContentChannel::Facebook => AssetType::FacebookPage,
            ContentChannel::Instagram => AssetType::InstagramAccount,
        };

        return ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('asset_type', $assetType)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function resolveForDraft(Workspace $workspace, ContentChannel $channel): ConnectedAsset
    {
        $assets = $this->assetsForChannel($workspace, $channel);

        if ($assets->isEmpty()) {
            throw new \RuntimeException(__('app.content.errors.no_connected_asset'));
        }

        if ($assets->count() > 1) {
            throw new \RuntimeException(__('app.content.errors.multiple_assets'));
        }

        return $assets->first();
    }
}
