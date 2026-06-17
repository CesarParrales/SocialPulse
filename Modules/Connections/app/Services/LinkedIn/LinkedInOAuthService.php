<?php

namespace Modules\Connections\Services\LinkedIn;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class LinkedInOAuthService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = $this->configResolver->linkedin($workspace->agency_id);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $this->redirectUri($config),
            'scope' => implode(' ', $config['scopes']),
            'state' => $this->encodeState($workspace->id, $userId),
        ]);

        return 'https://www.linkedin.com/oauth/v2/authorization?'.$query;
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
        $config = $this->configResolver->linkedin($workspace->agency_id);
        $tokens = $this->requestToken($config, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri($config),
        ]);

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::LinkedIn,
            ],
            [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds((int) ($tokens['expires_in'] ?? 5184000)),
                'status' => ConnectionStatus::Active,
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
        $config = $this->configResolver->linkedin($workspace->agency_id);

        return $this->requestToken($config, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, string>  $payload
     * @return array{access_token: string, expires_in: int, refresh_token?: string|null, scope?: string|null, token_type?: string|null}
     */
    private function requestToken(array $config, array $payload): array
    {
        $response = Http::timeout(30)
            ->asForm()
            ->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                ...$payload,
            ])
            ->throw()
            ->json();

        if (! is_array($response) || ! isset($response['access_token'])) {
            throw new RuntimeException('Respuesta OAuth de LinkedIn inválida.');
        }

        return [
            'access_token' => (string) $response['access_token'],
            'expires_in' => (int) ($response['expires_in'] ?? 5184000),
            'refresh_token' => $response['refresh_token'] ?? null,
            'scope' => $response['scope'] ?? null,
            'token_type' => $response['token_type'] ?? 'Bearer',
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
        return $config['redirect_uri'] ?? url('/connections/linkedin/callback');
    }
}
