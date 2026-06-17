<?php

namespace Modules\Connections\Services\Google;

use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use RuntimeException;

class GoogleTokenRefreshService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    public function refresh(PlatformConnection $connection): PlatformConnection
    {
        if ($connection->refresh_token === null || $connection->refresh_token === '') {
            throw new RuntimeException('La conexión Google no tiene refresh token.');
        }

        $connection->loadMissing('workspace');
        $config = $this->configResolver->google($connection->workspace?->agency_id);

        $tokens = Http::timeout(30)
            ->asForm()
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'refresh_token' => $connection->refresh_token,
                'grant_type' => 'refresh_token',
            ])
            ->throw()
            ->json();

        $connection->update([
            'access_token' => $tokens['access_token'],
            'token_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            'status' => ConnectionStatus::Active,
            'metadata' => array_merge($connection->metadata ?? [], [
                'last_token_refresh_at' => now()->toIso8601String(),
            ]),
        ]);

        return $connection->fresh();
    }
}
