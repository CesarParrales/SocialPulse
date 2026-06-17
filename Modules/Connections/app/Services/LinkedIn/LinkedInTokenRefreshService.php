<?php

namespace Modules\Connections\Services\LinkedIn;

use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\PlatformConnection;
use RuntimeException;

class LinkedInTokenRefreshService
{
    public function __construct(
        private readonly LinkedInOAuthService $oauth,
    ) {}

    public function refresh(PlatformConnection $connection): PlatformConnection
    {
        $refreshToken = $connection->refresh_token;

        if ($refreshToken === null || $refreshToken === '') {
            throw new RuntimeException('La conexión LinkedIn no tiene refresh token.');
        }

        $connection->loadMissing('workspace');
        $workspace = $connection->workspace;

        if ($workspace === null) {
            throw new RuntimeException('La conexión LinkedIn no tiene workspace asociado.');
        }

        $refreshed = $this->oauth->refreshToken($workspace, $refreshToken);

        $connection->update([
            'access_token' => $refreshed['access_token'],
            'refresh_token' => $refreshed['refresh_token'] ?? $refreshToken,
            'token_expires_at' => now()->addSeconds($refreshed['expires_in']),
            'status' => ConnectionStatus::Active,
            'metadata' => array_merge($connection->metadata ?? [], [
                'last_token_refresh_at' => now()->toIso8601String(),
            ]),
        ]);

        return $connection->fresh();
    }
}
