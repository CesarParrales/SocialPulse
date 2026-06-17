<?php

namespace Modules\Connections\Services\Meta;

use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\MetaAuthMode;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class MetaSystemUserService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    public function isConfigured(?int $agencyId = null): bool
    {
        return $this->configResolver->isMetaSystemUserConfigured($agencyId);
    }

    public function connect(Workspace $workspace): PlatformConnection
    {
        if (! $this->isConfigured($workspace->agency_id)) {
            throw new RuntimeException('Meta System User no está configurado.');
        }

        $config = $this->configResolver->meta($workspace->agency_id);
        $token = (string) $config['system_user_access_token'];
        $businessId = (string) $config['business_id'];

        $this->assertTokenCanAccessBusiness($config, $token, $businessId);

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::Meta,
            ],
            [
                'access_token' => $token,
                'refresh_token' => null,
                'token_expires_at' => null,
                'status' => ConnectionStatus::Active,
                'external_account_id' => $config['system_user_id'] ?? null,
                'external_account_name' => $businessId,
                'metadata' => [
                    'auth_mode' => MetaAuthMode::SystemUser->value,
                    'business_id' => $businessId,
                    'system_user_id' => $config['system_user_id'] ?? null,
                ],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function assertTokenCanAccessBusiness(array $config, string $token, string $businessId): void
    {
        Http::timeout(30)
            ->get($this->graphUrl($config, $businessId.'/owned_pages'), [
                'access_token' => $token,
                'limit' => 1,
                'fields' => 'id',
            ])
            ->throw();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function graphUrl(array $config, string $path): string
    {
        return 'https://graph.facebook.com/'.$config['api_version'].'/'.$path;
    }
}
