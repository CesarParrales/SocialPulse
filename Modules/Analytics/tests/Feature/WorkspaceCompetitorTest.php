<?php

namespace Modules\Analytics\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Analytics\Models\CompetitorAccount;
use Modules\Analytics\Models\CompetitorInsight;
use Modules\Analytics\Services\CompetitorInsightPromptBuilder;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceCompetitorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_competitors_index_page(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->get(route('workspaces.competitors.index', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Analytics/Competitors')
                ->where('canManage', true)
            );
    }

    public function test_operator_can_create_competitor_and_generate_prompt(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'comp-post',
            'post_type' => 'feed',
            'published_at' => now()->subDays(3),
            'content_preview' => 'Post competidor',
            'raw_metrics' => ['reach' => 1200, 'engagement' => 80],
            'captured_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.competitors.store', $workspace), [
                'name' => 'Competidor A',
                'platform' => 'instagram',
                'handle' => '@competidor',
                'followers_count' => 50000,
                'avg_reach' => 8000,
                'avg_engagement_rate' => 2.5,
                'data_source_note' => 'Informe manual Jun 2026',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('competitor_accounts', [
            'workspace_id' => $workspace->id,
            'name' => 'Competidor A',
        ]);

        $this->actingAs($admin)
            ->post(route('workspaces.competitors.prompt', $workspace))
            ->assertRedirect();

        $insight = CompetitorInsight::query()->where('workspace_id', $workspace->id)->first();
        $this->assertNotNull($insight);
        $this->assertStringContainsString('Competidor A', (string) $insight->prompt_text);
        $this->assertStringContainsString('CLIENTE', (string) $insight->prompt_text);
    }

    public function test_save_reviewed_insight(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->put(route('workspaces.competitors.insight', $workspace), [
                'ai_draft_text' => 'Borrador IA de prueba.',
                'reviewed_text' => 'Texto final revisado para el informe.',
            ])
            ->assertRedirect();

        $insight = CompetitorInsight::query()->where('workspace_id', $workspace->id)->first();
        $this->assertTrue($insight?->isReviewed());
        $this->assertSame('Texto final revisado para el informe.', $insight?->reportText());
    }

    public function test_prompt_builder_includes_manual_disclaimer(): void
    {
        [$workspace, , $fbAsset] = $this->workspaceContext();

        CompetitorAccount::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Rival',
            'avg_reach' => 1000,
            'data_source_note' => 'Deck Q2',
        ]);

        $prompt = app(CompetitorInsightPromptBuilder::class)->build(
            $workspace,
            ConnectedAsset::query()->whereKey($fbAsset->id)->get(),
        );

        $this->assertStringContainsString('estimaciones manuales', $prompt);
        $this->assertStringContainsString('Rival', $prompt);
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Competidores',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Competidores',
            'industry_category' => 'food',
            'region' => 'latam',
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

        $fbAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-comp',
            'name' => 'Facebook',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $fbAsset];
    }
}
