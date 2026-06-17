<?php

namespace Modules\Connections\Services\Meta;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\MetaAuthMode;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use RuntimeException;

class MetaGraphService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverAssets(PlatformConnection $connection): Collection
    {
        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión Meta no tiene token válido.');
        }

        $agencyId = $this->agencyId($connection);

        if ($this->usesSystemUser($connection)) {
            return $this->discoverViaSystemUser(
                $token,
                $this->businessId($connection, $agencyId),
                $agencyId,
            );
        }

        return $this->discoverViaUserOAuth($token, $agencyId);
    }

    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    private function discoverViaUserOAuth(string $token, ?int $agencyId): Collection
    {
        $assets = collect();

        $pages = Http::timeout(30)
            ->get($this->graphUrl('me/accounts', $agencyId), [
                'access_token' => $token,
                'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            ])
            ->throw()
            ->json('data') ?? [];

        $assets = $assets->merge($this->mapPages($pages));

        $adAccounts = Http::timeout(30)
            ->get($this->graphUrl('me/adaccounts', $agencyId), [
                'access_token' => $token,
                'fields' => 'id,name,account_status',
            ])
            ->throw()
            ->json('data') ?? [];

        return $assets->merge($this->mapAdAccounts($adAccounts));
    }

    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    private function discoverViaSystemUser(string $token, string $businessId, ?int $agencyId): Collection
    {
        $assets = collect();

        $pages = Http::timeout(30)
            ->get($this->graphUrl($businessId.'/owned_pages', $agencyId), [
                'access_token' => $token,
                'fields' => 'id,name,access_token,instagram_business_account{id,username}',
            ])
            ->throw()
            ->json('data') ?? [];

        if ($pages === []) {
            $pages = Http::timeout(30)
                ->get($this->graphUrl($businessId.'/client_pages', $agencyId), [
                    'access_token' => $token,
                    'fields' => 'id,name,access_token,instagram_business_account{id,username}',
                ])
                ->throw()
                ->json('data') ?? [];
        }

        $assets = $assets->merge($this->mapPages($pages));

        $adAccounts = Http::timeout(30)
            ->get($this->graphUrl($businessId.'/owned_ad_accounts', $agencyId), [
                'access_token' => $token,
                'fields' => 'id,name,account_status',
            ])
            ->throw()
            ->json('data') ?? [];

        if ($adAccounts === []) {
            $adAccounts = Http::timeout(30)
                ->get($this->graphUrl($businessId.'/client_ad_accounts', $agencyId), [
                    'access_token' => $token,
                    'fields' => 'id,name,account_status',
                ])
                ->throw()
                ->json('data') ?? [];
        }

        return $assets->merge($this->mapAdAccounts($adAccounts));
    }

    /**
     * @param  list<array<string, mixed>>  $pages
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    private function mapPages(array $pages): Collection
    {
        $assets = collect();

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

        return $assets;
    }

    /**
     * @param  list<array<string, mixed>>  $adAccounts
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    private function mapAdAccounts(array $adAccounts): Collection
    {
        $assets = collect();

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

    private function usesSystemUser(PlatformConnection $connection): bool
    {
        return ($connection->metadata['auth_mode'] ?? MetaAuthMode::UserOAuth->value)
            === MetaAuthMode::SystemUser->value;
    }

    private function businessId(PlatformConnection $connection, ?int $agencyId): string
    {
        $businessId = $connection->metadata['business_id']
            ?? $this->configResolver->meta($agencyId)['business_id'];

        if (! is_string($businessId) || $businessId === '') {
            throw new RuntimeException('La conexión Meta System User no tiene business_id.');
        }

        return $businessId;
    }

    private function agencyId(PlatformConnection $connection): ?int
    {
        $connection->loadMissing('workspace');

        return $connection->workspace?->agency_id;
    }

    private function graphUrl(string $path, ?int $agencyId): string
    {
        $version = $this->configResolver->meta($agencyId)['api_version'];

        return 'https://graph.facebook.com/'.$version.'/'.$path;
    }
}
