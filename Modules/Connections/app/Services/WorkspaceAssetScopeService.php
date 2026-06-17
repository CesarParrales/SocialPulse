<?php

namespace Modules\Connections\Services;

use Illuminate\Support\Collection;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Workspaces\Models\Workspace;

class WorkspaceAssetScopeService
{
    /**
     * @return array{
     *     all: Collection<int, ConnectedAsset>,
     *     selected: Collection<int, ConnectedAsset>,
     *     selected_asset_id: int|null
     * }
     */
    public function resolve(Workspace $workspace, ?int $assetId = null): array
    {
        $all = ConnectedAsset::query()
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->where('is_active', true)
            ->with('connection:id,platform')
            ->orderBy('name')
            ->get(['id', 'connection_id', 'asset_type', 'platform_asset_id', 'name']);

        if ($assetId === null) {
            return [
                'all' => $all,
                'selected' => $all,
                'selected_asset_id' => null,
            ];
        }

        $selected = $all->where('id', $assetId)->values();

        if ($selected->isEmpty()) {
            return [
                'all' => $all,
                'selected' => $all,
                'selected_asset_id' => null,
            ];
        }

        return [
            'all' => $all,
            'selected' => $selected,
            'selected_asset_id' => $assetId,
        ];
    }

    /**
     * @param  Collection<int, ConnectedAsset>  $assets
     * @return list<array{id: int, name: string, asset_type: string, platform: string|null}>
     */
    public function serializeForFrontend(Collection $assets): array
    {
        return $assets->map(fn (ConnectedAsset $asset) => [
            'id' => $asset->id,
            'name' => $asset->name,
            'asset_type' => $asset->asset_type->value,
            'platform' => $asset->connection?->platform->value,
        ])->values()->all();
    }
}
