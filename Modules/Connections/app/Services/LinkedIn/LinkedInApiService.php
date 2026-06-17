<?php

namespace Modules\Connections\Services\LinkedIn;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\PlatformConnection;
use Modules\Settings\Services\IntegrationConfigResolver;
use RuntimeException;

class LinkedInApiService
{
    public function __construct(
        private readonly IntegrationConfigResolver $configResolver,
    ) {}

    /**
     * @return Collection<int, array{type: string, id: string, name: string, metadata: array<string, mixed>}>
     */
    public function discoverAssets(PlatformConnection $connection): Collection
    {
        $token = $connection->access_token;

        if ($token === null || $token === '') {
            throw new RuntimeException('La conexión LinkedIn no tiene token válido.');
        }

        $response = Http::timeout(30)
            ->withToken($token)
            ->get('https://api.linkedin.com/v2/organizationAcls', [
                'q' => 'roleAssignee',
                'role' => 'ADMINISTRATOR',
                'projection' => '(elements*(organization~(localizedName,id)))',
            ])
            ->throw()
            ->json('elements') ?? [];

        return collect($response)
            ->map(function (array $element): ?array {
                $organization = $element['organization~'] ?? null;

                if (! is_array($organization)) {
                    return null;
                }

                $organizationId = $organization['id'] ?? null;
                $name = $organization['localizedName'] ?? 'LinkedIn Page';

                if (! is_numeric($organizationId)) {
                    return null;
                }

                return [
                    'type' => AssetType::LinkedInPage->value,
                    'id' => (string) $organizationId,
                    'name' => is_string($name) ? $name : 'LinkedIn Page',
                    'metadata' => [
                        'organization_urn' => 'urn:li:organization:'.$organizationId,
                    ],
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function fetchRecentPosts(string $accessToken, string $organizationId, ?int $agencyId = null, int $count = 20): array
    {
        $config = $this->configResolver->linkedin($agencyId);
        $author = str_starts_with($organizationId, 'urn:li:organization:')
            ? $organizationId
            : 'urn:li:organization:'.$organizationId;

        $response = Http::timeout(30)
            ->withToken($accessToken)
            ->withHeaders([
                'LinkedIn-Version' => $config['api_version'] ?? '202405',
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->get('https://api.linkedin.com/rest/posts', [
                'q' => 'author',
                'author' => $author,
                'count' => $count,
            ])
            ->throw()
            ->json('elements');

        return is_array($response) ? $response : [];
    }
}
