<?php

namespace Modules\Connections\Services\YouTube;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\PlatformConnection;
use RuntimeException;

class YouTubeApiService
{
    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverAssets(PlatformConnection $connection): Collection
    {
        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión YouTube no tiene token válido.');
        }

        return $this->discoverChannels($token);
    }

    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverChannels(string $accessToken): Collection
    {
        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet,contentDetails',
                'mine' => 'true',
            ])
            ->throw()
            ->json('items') ?? [];

        return collect($response)
            ->map(function (array $channel): ?array {
                $channelId = $channel['id'] ?? null;
                $title = $channel['snippet']['title'] ?? 'YouTube Channel';
                $uploadsPlaylistId = $channel['contentDetails']['relatedPlaylists']['uploads'] ?? null;

                if (! is_string($channelId) || $channelId === '') {
                    return null;
                }

                return [
                    'type' => AssetType::YouTubeChannel->value,
                    'id' => $channelId,
                    'name' => is_string($title) ? $title : 'YouTube Channel',
                    'metadata' => [
                        'uploads_playlist_id' => $uploadsPlaylistId,
                        'thumbnail_url' => $channel['snippet']['thumbnails']['default']['url'] ?? null,
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentVideos(string $accessToken, string $channelId, ?string $uploadsPlaylistId = null, int $maxResults = 20): array
    {
        if ($uploadsPlaylistId === null || $uploadsPlaylistId === '') {
            $uploadsPlaylistId = $this->uploadsPlaylistId($accessToken, $channelId);
        }

        if ($uploadsPlaylistId === null) {
            return [];
        }

        $playlistItems = Http::timeout(30)
            ->withToken($accessToken)
            ->get('https://www.googleapis.com/youtube/v3/playlistItems', [
                'part' => 'snippet,contentDetails',
                'playlistId' => $uploadsPlaylistId,
                'maxResults' => $maxResults,
            ])
            ->throw()
            ->json('items') ?? [];

        $videoIds = collect($playlistItems)
            ->map(fn (array $item) => $item['contentDetails']['videoId'] ?? null)
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values()
            ->all();

        if ($videoIds === []) {
            return [];
        }

        $videos = Http::timeout(30)
            ->withToken($accessToken)
            ->get('https://www.googleapis.com/youtube/v3/videos', [
                'part' => 'snippet,statistics',
                'id' => implode(',', $videoIds),
            ])
            ->throw()
            ->json('items') ?? [];

        return is_array($videos) ? $videos : [];
    }

    private function uploadsPlaylistId(string $accessToken, string $channelId): ?string
    {
        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'contentDetails',
                'id' => $channelId,
            ])
            ->throw()
            ->json('items.0.contentDetails.relatedPlaylists.uploads');

        return is_string($response) && $response !== '' ? $response : null;
    }
}
