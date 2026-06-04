<?php

namespace Modules\Connections\Services\Meta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class MetaOAuthService
{
    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = config('connections.meta');

        $query = http_build_query([
            'client_id' => $config['app_id'],
            'redirect_uri' => $this->redirectUri(),
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
        $shortLived = $this->exchangeCodeForToken($code);
        $longLived = $this->exchangeForLongLivedToken($shortLived['access_token']);

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
                    'token_type' => $longLived['token_type'] ?? 'bearer',
                ],
            ],
        );
    }

    /**
     * @return array{access_token: string, user_id?: string, expires_in?: int, token_type?: string}
     */
    private function exchangeCodeForToken(string $code): array
    {
        $config = config('connections.meta');

        $response = Http::timeout(30)
            ->get($this->graphUrl('oauth/access_token'), [
                'client_id' => $config['app_id'],
                'client_secret' => $config['app_secret'],
                'redirect_uri' => $this->redirectUri(),
                'code' => $code,
            ])
            ->throw()
            ->json();

        return $response;
    }

    /**
     * @return array{access_token: string, expires_in?: int, token_type?: string}
     */
    private function exchangeForLongLivedToken(string $shortLivedToken): array
    {
        $config = config('connections.meta');

        return Http::timeout(30)
            ->get($this->graphUrl('oauth/access_token'), [
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

    private function redirectUri(): string
    {
        return config('connections.meta.redirect_uri')
            ?? url('/connections/meta/callback');
    }

    private function graphUrl(string $path): string
    {
        $version = config('connections.meta.api_version');

        return 'https://graph.facebook.com/'.$version.'/'.$path;
    }
}
