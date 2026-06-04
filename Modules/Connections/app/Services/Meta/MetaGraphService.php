<?php

namespace Modules\Connections\Services\Meta;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\PlatformConnection;
use RuntimeException;

class MetaGraphService
{
    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverAssets(PlatformConnection $connection): Collection
    {
        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión Meta no tiene token válido.');
        }

        $assets = collect();

        $pages = Http::timeout(30)
            ->get($this->graphUrl('me/accounts'), [
                'access_token' => $token,
                'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            ])
            ->throw()
            ->json('data') ?? [];

        foreach ($pages as $page) {
            $assets->push([
                'type' => AssetType::FacebookPage->value,
                'id' => (string) $page['id'],
                'name' => (string) $page['name'],
                'metadata' => [
                    'page_access_token' => $page['access_token'] ?? null,
                ],
            ]);

            $instagram = $page['instagram_business_account'] ?? null;

            if (is_array($instagram) && isset($instagram['id'])) {
                $assets->push([
                    'type' => AssetType::InstagramAccount->value,
                    'id' => (string) $instagram['id'],
                    'name' => (string) ($instagram['username'] ?? $instagram['id']),
                    'metadata' => [
                        'linked_page_id' => (string) $page['id'],
                    ],
                ]);
            }
        }

        $adAccounts = Http::timeout(30)
            ->get($this->graphUrl('me/adaccounts'), [
                'access_token' => $token,
                'fields' => 'id,name,account_status',
            ])
            ->throw()
            ->json('data') ?? [];

        foreach ($adAccounts as $account) {
            $assets->push([
                'type' => AssetType::MetaAds->value,
                'id' => (string) $account['id'],
                'name' => (string) ($account['name'] ?? $account['id']),
                'metadata' => [
                    'account_status' => $account['account_status'] ?? null,
                ],
            ]);
        }

        return $assets;
    }

    private function graphUrl(string $path): string
    {
        $version = config('connections.meta.api_version');

        return 'https://graph.facebook.com/'.$version.'/'.$path;
    }
}
