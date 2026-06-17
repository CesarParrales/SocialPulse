<?php

namespace Modules\Ingestion\LinkedIn;

use Modules\Connections\Services\LinkedIn\LinkedInApiService;

class LinkedInOrganicClient
{
    public function __construct(
        private readonly LinkedInApiService $api,
    ) {}

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentPosts(string $accessToken, string $organizationId, ?int $agencyId = null, int $count = 20): array
    {
        return $this->api->fetchRecentPosts($accessToken, $organizationId, $agencyId, $count);
    }
}
