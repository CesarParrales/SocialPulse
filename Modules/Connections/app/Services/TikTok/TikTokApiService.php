<?php

namespace Modules\Connections\Services\TikTok;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\PlatformConnection;
use RuntimeException;

class TikTokApiService
{
    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverAssets(PlatformConnection $connection): Collection
    {
        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión TikTok no tiene token válido.');
        }

        $profile = $this->fetchUserProfile($token);
        $openId = $profile['open_id'] ?? $connection->external_account_id;

        if (! is_string($openId) || $openId === '') {
            throw new RuntimeException('No se pudo resolver la cuenta TikTok conectada.');
        }

        $displayName = $profile['display_name'] ?? $profile['username'] ?? $connection->external_account_name ?? 'TikTok';

        return collect([[
            'type' => AssetType::TikTokAccount->value,
            'id' => $openId,
            'name' => is_string($displayName) ? $displayName : 'TikTok',
            'metadata' => [
                'username' => $profile['username'] ?? null,
                'avatar_url' => $profile['avatar_url'] ?? null,
            ],
        ]]);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserProfile(string $accessToken): array
    {
        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->get('https://open.tiktokapis.com/v2/user/info/', [
                'fields' => 'open_id,union_id,avatar_url,display_name,username',
            ])
            ->throw()
            ->json('data.user');

        return is_array($response) ? $response : [];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentVideos(string $accessToken, int $maxCount = 20): array
    {
        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->asJson()
            ->post('https://open.tiktokapis.com/v2/video/list/?fields=id,title,create_time,cover_image_url,share_url,view_count,like_count,comment_count,share_count', [
                'max_count' => $maxCount,
            ])
            ->throw()
            ->json('data.videos');

        return is_array($response) ? $response : [];
    }
}
