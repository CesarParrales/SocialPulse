<?php

namespace Modules\Connections\Services\TikTok;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class TikTokOAuthService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
        private readonly TikTokApiService $api,
    ) {}

    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = $this->configResolver->tiktok($workspace->agency_id);

        $query = http_build_query([
            'client_key' => $config['client_key'],
            'scope' => implode(',', $config['scopes']),
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri($config),
            'state' => $this->encodeState($workspace->id, $userId),
        ]);

        return 'https://www.tiktok.com/v2/auth/authorize/?'.$query;
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
        $config = $this->configResolver->tiktok($workspace->agency_id);

        $response = Http::timeout(30)
            ->asJson()
            ->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key' => $config['client_key'],
                'client_secret' => $config['client_secret'],
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri($config),
            ])
            ->throw()
            ->json('data');

        if (! is_array($response) || ! isset($response['access_token'])) {
            throw new RuntimeException('Respuesta OAuth de TikTok inválida.');
        }

        $accessToken = (string) $response['access_token'];
        $openId = isset($response['open_id']) ? (string) $response['open_id'] : null;
        $profile = $this->api->fetchUserProfile($accessToken);
        $displayName = $profile['display_name'] ?? $profile['username'] ?? 'TikTok';

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::TikTok,
            ],
            [
                'access_token' => $accessToken,
                'refresh_token' => $response['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds((int) ($response['expires_in'] ?? 86400)),
                'status' => ConnectionStatus::Active,
                'external_account_id' => $openId ?? ($profile['open_id'] ?? null),
                'external_account_name' => is_string($displayName) ? $displayName : 'TikTok',
                'metadata' => [
                    'scope' => $response['scope'] ?? null,
                    'token_type' => $response['token_type'] ?? 'Bearer',
                    'open_id' => $openId ?? ($profile['open_id'] ?? null),
                    'username' => $profile['username'] ?? null,
                ],
            ],
        );
    }

    /**
     * @return array{access_token: string, expires_in: int, refresh_token?: string|null}
     */
    public function refreshToken(Workspace $workspace, string $refreshToken): array
    {
        $config = $this->configResolver->tiktok($workspace->agency_id);

        $response = Http::timeout(30)
            ->asJson()
            ->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key' => $config['client_key'],
                'client_secret' => $config['client_secret'],
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ])
            ->throw()
            ->json('data');

        if (! is_array($response) || ! isset($response['access_token'])) {
            throw new RuntimeException('No se pudo refrescar el token de TikTok.');
        }

        return [
            'access_token' => (string) $response['access_token'],
            'expires_in' => (int) ($response['expires_in'] ?? 86400),
            'refresh_token' => $response['refresh_token'] ?? null,
        ];
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
        return $config['redirect_uri'] ?? url('/connections/tiktok/callback');
    }
}
