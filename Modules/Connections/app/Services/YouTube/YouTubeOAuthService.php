<?php

namespace Modules\Connections\Services\YouTube;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class YouTubeOAuthService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
        private readonly YouTubeApiService $api,
    ) {}

    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = $this->configResolver->youtube($workspace->agency_id);

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $this->redirectUri($config),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'scope' => implode(' ', $config['scopes']),
            'state' => $this->encodeState($workspace->id, $userId),
        ]);

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.$query;
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
        $config = $this->configResolver->youtube($workspace->agency_id);

        $tokens = Http::timeout(30)
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $this->redirectUri($config),
                'grant_type' => 'authorization_code',
            ])
            ->throw()
            ->json();

        if (! is_array($tokens) || ! isset($tokens['access_token'])) {
            throw new RuntimeException('Respuesta OAuth de YouTube inválida.');
        }

        $accessToken = (string) $tokens['access_token'];
        $channels = $this->api->discoverChannels($accessToken);
        $primary = $channels->first();
        $displayName = is_array($primary) ? ($primary['name'] ?? 'YouTube') : 'YouTube';
        $externalId = is_array($primary) ? ($primary['id'] ?? null) : null;

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::YouTube,
            ],
            [
                'access_token' => $accessToken,
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 3600)),
                'status' => ConnectionStatus::Active,
                'external_account_id' => is_string($externalId) ? $externalId : null,
                'external_account_name' => is_string($displayName) ? $displayName : 'YouTube',
                'metadata' => [
                    'scope' => $tokens['scope'] ?? null,
                    'token_type' => $tokens['token_type'] ?? 'Bearer',
                ],
            ],
        );
    }

    /**
     * @return array{access_token: string, expires_in: int, refresh_token?: string|null}
     */
    public function refreshToken(Workspace $workspace, string $refreshToken): array
    {
        $config = $this->configResolver->youtube($workspace->agency_id);

        $response = Http::timeout(30)
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ])
            ->throw()
            ->json();

        if (! is_array($response) || ! isset($response['access_token'])) {
            throw new RuntimeException('No se pudo refrescar el token de YouTube.');
        }

        return [
            'access_token' => (string) $response['access_token'],
            'expires_in' => (int) ($response['expires_in'] ?? 3600),
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
        return $config['redirect_uri'] ?? url('/connections/youtube/callback');
    }
}
