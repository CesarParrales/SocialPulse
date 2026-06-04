<?php

namespace Modules\Connections\Services\Google;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Models\Workspace;
use RuntimeException;

class GoogleOAuthService
{
    public function authorizationUrl(Workspace $workspace, int $userId): string
    {
        $config = config('connections.google');

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $this->redirectUri(),
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
        $config = config('connections.google');

        $tokens = Http::timeout(30)
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
            ])
            ->throw()
            ->json();

        return PlatformConnection::query()->updateOrCreate(
            [
                'workspace_id' => $workspace->id,
                'platform' => Platform::Google,
            ],
            [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
                'status' => ConnectionStatus::Active,
                'metadata' => [
                    'scope' => $tokens['scope'] ?? null,
                    'token_type' => $tokens['token_type'] ?? 'Bearer',
                ],
            ],
        );
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
        return config('connections.google.redirect_uri')
            ?? url('/connections/google/callback');
    }
}
