<?php

namespace Modules\Connections\Services;

use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\Google\GoogleTokenRefreshService;
use Modules\Connections\Services\LinkedIn\LinkedInTokenRefreshService;
use Modules\Connections\Services\Meta\MetaOAuthService;
use Modules\Connections\Services\TikTok\TikTokTokenRefreshService;
use Modules\Connections\Services\YouTube\YouTubeTokenRefreshService;
use RuntimeException;

class PlatformTokenRefreshService
{
    public function __construct(
        private readonly MetaOAuthService $metaOAuth,
        private readonly GoogleTokenRefreshService $googleRefresh,
        private readonly TikTokTokenRefreshService $tiktokRefresh,
        private readonly LinkedInTokenRefreshService $linkedinRefresh,
        private readonly YouTubeTokenRefreshService $youtubeRefresh,
    ) {}

    public function needsRefresh(PlatformConnection $connection, int $withinDays = 7): bool
    {
        if ($connection->status !== ConnectionStatus::Active) {
            return false;
        }

        if ($connection->usesMetaSystemUser()) {
            return false;
        }

        if ($connection->token_expires_at === null) {
            return false;
        }

        return $connection->token_expires_at->lte(now()->addDays($withinDays));
    }

    public function refresh(PlatformConnection $connection): PlatformConnection
    {
        return match ($connection->platform) {
            Platform::Meta => $this->refreshMeta($connection),
            Platform::Google => $this->googleRefresh->refresh($connection),
            Platform::TikTok => $this->tiktokRefresh->refresh($connection),
            Platform::LinkedIn => $this->linkedinRefresh->refresh($connection),
            Platform::YouTube => $this->youtubeRefresh->refresh($connection),
        };
    }

    private function refreshMeta(PlatformConnection $connection): PlatformConnection
    {
        if ($connection->usesMetaSystemUser()) {
            return $connection;
        }

        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión Meta no tiene token válido.');
        }

        $connection->loadMissing('workspace');
        $workspace = $connection->workspace;

        if ($workspace === null) {
            throw new RuntimeException('La conexión Meta no tiene workspace asociado.');
        }

        $refreshed = $this->metaOAuth->refreshLongLivedToken($workspace, $token);

        $connection->update([
            'access_token' => $refreshed['access_token'],
            'token_expires_at' => now()->addSeconds($refreshed['expires_in'] ?? 5184000),
            'status' => ConnectionStatus::Active,
            'metadata' => array_merge($connection->metadata ?? [], [
                'token_type' => $refreshed['token_type'] ?? 'bearer',
                'last_token_refresh_at' => now()->toIso8601String(),
            ]),
        ]);

        return $connection->fresh();
    }
}
