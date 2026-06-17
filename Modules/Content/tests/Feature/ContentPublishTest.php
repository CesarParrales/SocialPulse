<?php

namespace Modules\Content\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Models\PublishedContentLink;
use Modules\Content\Services\PublishedContentLinkService;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class ContentPublishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_publish_facebook_feed_creates_link_and_organic_post(): void
    {
        [$workspace, $admin, $asset] = $this->facebookContext();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['id' => '123456789_987654321'], 200),
        ]);

        $draft = ContentDraft::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Post FB',
            'caption' => 'Hola desde SocialPulse',
            'channel' => 'facebook',
            'content_type' => 'feed',
            'status' => ContentDraftStatus::Approved,
            'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.content.drafts.publish', [$workspace, $draft]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $draft->refresh();

        $this->assertSame(ContentDraftStatus::Published, $draft->status);
        $this->assertSame('123456789_987654321', $draft->platform_post_id);

        $this->assertDatabaseHas('organic_posts', [
            'asset_id' => $asset->id,
            'platform_post_id' => '123456789_987654321',
        ]);

        $organicPost = OrganicPost::query()->first();
        $this->assertNotNull($organicPost);

        $this->assertDatabaseHas('published_content_links', [
            'content_draft_id' => $draft->id,
            'organic_post_id' => $organicPost->id,
            'platform_post_id' => '123456789_987654321',
        ]);
    }

    public function test_ingestion_links_existing_published_content(): void
    {
        [$workspace, $admin, $asset] = $this->facebookContext();

        $draft = ContentDraft::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Post link',
            'caption' => 'Caption',
            'channel' => 'facebook',
            'content_type' => 'feed',
            'status' => ContentDraftStatus::Published,
            'platform_post_id' => '111_222',
            'created_by' => $admin->id,
        ]);

        PublishedContentLink::query()->create([
            'content_draft_id' => $draft->id,
            'organic_post_id' => null,
            'asset_id' => $asset->id,
            'platform_post_id' => '111_222',
            'published_at' => now(),
        ]);

        $organicPost = OrganicPost::query()->create([
            'asset_id' => $asset->id,
            'platform_post_id' => '111_222',
            'post_type' => 'feed',
            'published_at' => now(),
            'raw_metrics' => ['reach' => 50],
            'captured_at' => now(),
        ]);

        app(PublishedContentLinkService::class)
            ->attachOrganicPost($organicPost);

        $this->assertDatabaseHas('published_content_links', [
            'content_draft_id' => $draft->id,
            'organic_post_id' => $organicPost->id,
        ]);
    }

    public function test_client_cannot_publish(): void
    {
        [$workspace, , $client] = $this->facebookAndClientContext();

        $draft = ContentDraft::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'No publish',
            'caption' => 'Texto',
            'channel' => 'facebook',
            'content_type' => 'feed',
            'status' => ContentDraftStatus::Approved,
        ]);

        $this->actingAs($client)
            ->post(route('workspaces.content.drafts.publish', [$workspace, $draft]))
            ->assertRedirect($client->clientHomeUrl());
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function facebookContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Publish',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Publish',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'status' => ConnectionStatus::Active,
        ]);

        $asset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-publish',
            'name' => 'Facebook',
            'is_active' => true,
            'metadata' => ['page_access_token' => 'page-token'],
        ]);

        return [$workspace, $admin, $asset];
    }

    /**
     * @return array{0: Workspace, 1: User, 2: User}
     */
    private function facebookAndClientContext(): array
    {
        [$workspace, $admin] = $this->facebookContext();

        $client = User::factory()->create(['agency_id' => $workspace->agency_id]);
        $client->assignRole(SystemRole::ClientReadonly->value);
        $client->workspaces()->attach($workspace->id, ['role' => 'client_readonly']);

        return [$workspace, $admin, $client];
    }
}
