<?php

namespace Modules\Ingestion\TikTok;

use Modules\Connections\Services\TikTok\TikTokApiService;

class TikTokOrganicClient
{
    public function __construct(
        private readonly TikTokApiService $api,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentVideos(string $accessToken, int $maxCount = 20): array
    {
        return $this->api->fetchRecentVideos($accessToken, $maxCount);
    }
}
