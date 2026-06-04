<?php

namespace Modules\Connections\Services;

use Illuminate\Validation\ValidationException;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;

class ConnectedAssetService
{
    /**
     * @param  list<array{type: string, id: string, name: string, selected: bool, metadata?: array<string, mixed>}>  $assets
     */
    public function syncSelection(PlatformConnection $connection, array $assets): void
    {
        foreach ($assets as $asset) {
            if (! ($asset['selected'] ?? false)) {
                ConnectedAsset::query()
                    ->where('connection_id', $connection->id)
                    ->where('asset_type', $asset['type'])
                    ->where('platform_asset_id', $asset['id'])
                    ->delete();

                continue;
            }

            $type = AssetType::from($asset['type']);

            $conflict = ConnectedAsset::query()
                ->where('asset_type', $type)
                ->where('platform_asset_id', $asset['id'])
                ->where('connection_id', '!=', $connection->id)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'assets' => "El activo {$asset['name']} ya está monitoreado en otro workspace.",
                ]);
            }

            ConnectedAsset::query()->updateOrCreate(
                [
                    'connection_id' => $connection->id,
                    'asset_type' => $type,
                    'platform_asset_id' => $asset['id'],
                ],
                [
                    'name' => $asset['name'],
                    'is_active' => true,
                    'metadata' => $asset['metadata'] ?? [],
                ],
            );
        }
    }
}
