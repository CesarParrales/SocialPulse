<?php

namespace Modules\Ingestion\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Ingestion\Services\OrganicLinkedInIngestionService;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class OrganicLinkedInIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organic_linkedin_ingestion_stores_posts(): void
    {
        $asset = $this->linkedInPageAsset('9001');

        Http::fake([
            'api.linkedin.com/rest/posts*' => Http::response([
                'elements' => [[
                    'id' => 'urn:li:share:12345',
                    'createdAt' => 1717200000000,
                    'commentary' => 'Actualización de marca',
                    'totalShareStatistics' => [
                        'impressionCount' => 4200,
                        'uniqueImpressionsCount' => 3100,
                        'likeCount' => 120,
                        'commentCount' => 8,
                        'shareCount' => 5,
                        'clickCount' => 42,
                    ],
                ]],
            ]),
        ]);

        $log = app(OrganicLinkedInIngestionService::class)->ingestAsset($asset);

        $this->assertSame(IngestionStatus::Success, $log->status);
        $this->assertSame(IngestionJobType::OrganicLinkedIn, $log->job_type);
        $this->assertSame(1, $log->records_ingested);

        $post = OrganicPost::query()->first();

        $this->assertNotNull($post);
        $this->assertSame('urn:li:share:12345', $post->platform_post_id);
        $this->assertSame(3100, (int) $post->raw_metrics['reach']);
    }

    private function linkedInPageAsset(string $organizationId): ConnectedAsset
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente LinkedIn',
            'timezone' => 'UTC',
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::LinkedIn,
            'access_token' => 'linkedin-token',
            'refresh_token' => 'linkedin-refresh',
            'token_expires_at' => now()->addMonth(),
            'status' => ConnectionStatus::Active,
        ]);

        return ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::LinkedInPage,
            'platform_asset_id' => $organizationId,
            'name' => 'Marca LinkedIn',
            'is_active' => true,
            'metadata' => [
                'organization_urn' => 'urn:li:organization:'.$organizationId,
            ],
        ]);
    }
}
