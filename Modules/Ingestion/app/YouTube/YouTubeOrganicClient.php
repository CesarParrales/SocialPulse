<?php

namespace Modules\Ingestion\YouTube;

use Modules\Connections\Services\YouTube\YouTubeApiService;

class YouTubeOrganicClient
{
    public function __construct(
        private readonly YouTubeApiService $api,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentVideos(
        string $accessToken,
        string $channelId,
        ?string $uploadsPlaylistId = null,
        int $maxResults = 20,
    ): array {
        return $this->api->fetchRecentVideos($accessToken, $channelId, $uploadsPlaylistId, $maxResults);
    }
}
