<?php

namespace Modules\Connections\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\MetaAuthMode;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class MetaOAuthService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = $this->configResolver->meta($workspace->agency_id);

        $query = http_build_query([
            'client_id' => $config['app_id'],
            'redirect_uri' => $this->redirectUri($config),
            'state' => $this->encodeState($workspace->id, $userId),
            'scope' => implode(',', $config['scopes']),
            'response_type' => 'code',
        ]);

        return 'https://www.facebook.com/'.$config['api_version'].'/dialog/oauth?'.$query;
    }

    /**
     * @return array{workspace_id: int, user_id: int}
     */
    public function decodeState(string $state): array
    {
        $payload = decrypt($state);

        if (! is_array($payload) || ! isset($payload['workspace_id'], $payload['user_id'])) {
            throw new RuntimeException('Estado OAuth inválido.');
        }

        return [
            'workspace_id' => (int) $payload['workspace_id'],
            'user_id' => (int) $payload['user_id'],
        ];
    }

    public function connect(Workspace $workspace, string $code): PlatformConnection
    {
        $shortLived = $this->exchangeCodeForToken($workspace, $code);
        $longLived = $this->refreshLongLivedToken($workspace, $shortLived['access_token']);

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::Meta,
            ],
            [
                'access_token' => $longLived['access_token'],
                'refresh_token' => null,
                'token_expires_at' => now()->addSeconds($longLived['expires_in'] ?? 5184000),
                'status' => ConnectionStatus::Active,
                'external_account_id' => $shortLived['user_id'] ?? null,
                'metadata' => [
                    'auth_mode' => MetaAuthMode::UserOAuth->value,
                    'token_type' => $longLived['token_type'] ?? 'bearer',
                ],
            ],
        );
    }

    /**
     * @return array{access_token: string, user_id?: string, expires_in?: int, token_type?: string}
     */
    private function exchangeCodeForToken(Workspace $workspace, string $code): array
    {
        $config = $this->configResolver->meta($workspace->agency_id);

        return Http::timeout(30)
            ->get($this->graphUrl($config, 'oauth/access_token'), [
                'client_id' => $config['app_id'],
                'client_secret' => $config['app_secret'],
                'redirect_uri' => $this->redirectUri($config),
                'code' => $code,
            ])
            ->throw()
            ->json();
    }

    /**
     * @return array{access_token: string, expires_in?: int, token_type?: string}
     */
    public function refreshLongLivedToken(Workspace $workspace, string $accessToken): array
    {
        return $this->exchangeForLongLivedToken($workspace, $accessToken);
    }

    /**
     * @return array{access_token: string, expires_in?: int, token_type?: string}
     */
    private function exchangeForLongLivedToken(Workspace $workspace, string $shortLivedToken): array
    {
        $config = $this->configResolver->meta($workspace->agency_id);

        return Http::timeout(30)
            ->get($this->graphUrl($config, 'oauth/access_token'), [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $config['app_id'],
                'client_secret' => $config['app_secret'],
                'fb_exchange_token' => $shortLivedToken,
            ])
            ->throw()
            ->json();
    }

    private function encodeState(int $workspaceId, int $userId): string
    {
        return encrypt([
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'nonce' => Str::random(16),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function redirectUri(array $config): string
    {
        return $config['redirect_uri'] ?? url('/connections/meta/callback');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function graphUrl(array $config, string $path): string
    {
        return 'https://graph.facebook.com/'.$config['api_version'].'/'.$path;
    }
}
