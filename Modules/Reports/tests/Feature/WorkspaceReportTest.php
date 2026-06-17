<?php

namespace Modules\Reports\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Models\AdCampaign;
use Modules\Ingestion\Models\AdMetricDaily;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Reports\Enums\ReportStatus;
use Modules\Reports\Models\Report;
use Modules\Reports\Services\ReportAppendixCsvExporter;
use Modules\Reports\Services\ReportAppendixExcelExporter;
use Modules\Reports\Services\ReportDataAssembler;
use Modules\Reports\Services\ReportNarrativeService;
use Modules\Reports\Support\ReportConfig;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
        Storage::fake('local');
    }

    public function test_report_index_page(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->get(route('workspaces.reports.index', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Index')
                ->has('reports')
            );
    }

    public function test_create_report_generates_pdf(): void
    {
        [$workspace, $admin, $fbAsset, $adsAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-report',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post de prueba',
            'raw_metrics' => ['reach' => 300, 'engagement' => 20],
            'captured_at' => now(),
        ]);

        $campaign = AdCampaign::query()->create([
            'asset_id' => $adsAsset->id,
            'platform_campaign_id' => 'camp-report',
            'name' => 'Campaña reporte',
        ]);

        AdMetricDaily::query()->create([
            'campaign_id' => $campaign->id,
            'date' => now()->subDay()->toDateString(),
            'placement' => 'Facebook Feed',
            'spend' => 25,
            'impressions' => 5000,
            'is_preliminary' => false,
            'captured_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('workspaces.reports.store', $workspace), [
                'title' => 'Reporte mensual demo',
                'period' => '30d',
                'primary_color' => '#4f46e5',
                'secondary_color' => '#818cf8',
                'sections' => [
                    'overview' => true,
                    'organic' => true,
                    'paid' => true,
                    'top_content' => true,
                    'comparisons' => true,
                ],
            ]);

        $report = Report::query()->first();
        $this->assertNotNull($report);

        $response->assertRedirect(route('workspaces.reports.show', [$workspace, $report]));

        $report->refresh();
        $this->assertSame(ReportStatus::Ready, $report->status);
        $this->assertNotNull($report->file_path);
        Storage::disk('local')->assertExists($report->file_path);

        $this->actingAs($admin)
            ->get(route('workspaces.reports.show', [$workspace, $report]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Reports/Show')
                ->where('report.download_ready', true)
                ->where('report.preview_url', fn ($url) => $url !== null)
            );

        $this->actingAs($admin)
            ->get(route('workspaces.reports.download', [$workspace, $report]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('workspaces.reports.preview', [$workspace, $report]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_config_normalizes_defaults(): void
    {
        $config = ReportConfig::normalize([
            'title' => 'Mi reporte',
            'primary_color' => 'invalid',
        ]);

        $this->assertSame('#4f46e5', $config['primary_color']);
        $this->assertTrue($config['sections']['overview']);
        $this->assertSame(ReportConfig::METRICS, $config['metrics']);
    }

    public function test_narrative_detects_reach_up_engagement_down(): void
    {
        $service = app(ReportNarrativeService::class);

        $result = $service->build(
            analytics: [
                'kpis' => [
                    'reach' => [
                        'current' => 10000,
                        'previous' => 8000,
                        'change_pct' => 25.0,
                        'direction' => 'up',
                        'comparable' => true,
                    ],
                    'engagement_rate' => [
                        'current' => 2.1,
                        'previous' => 3.0,
                        'change_pct' => -30.0,
                        'direction' => 'down',
                        'comparable' => true,
                    ],
                ],
            ],
            channelSections: [[
                'key' => 'facebook',
                'label' => 'Facebook Page',
                'kind' => 'organic',
                'kpis' => [
                    'reach' => [
                        'current' => 5000,
                        'previous' => 4000,
                        'change_pct' => 25.0,
                        'direction' => 'up',
                        'comparable' => true,
                    ],
                    'engagement_rate' => [
                        'current' => 1.8,
                        'previous' => 2.5,
                        'change_pct' => -28.0,
                        'direction' => 'down',
                        'comparable' => true,
                    ],
                ],
                'top_posts' => [],
                'top_reels' => [],
            ]],
        );

        $executiveText = implode(' ', $result['executive']['paragraphs']);
        $this->assertStringContainsString('interacción relativa cayó', $executiveText);

        $facebook = collect($result['channels'])->firstWhere('key', 'facebook');
        $this->assertNotNull($facebook);
        $this->assertContains('Alcance ↑ · Interacción ↓', $facebook['bullets']);
    }

    public function test_assembler_includes_appendix(): void
    {
        [$workspace, , $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-appendix',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post anexo',
            'raw_metrics' => [
                'reach' => 400,
                'impressions' => 500,
                'clicks' => 12,
                'video_views' => 30,
            ],
            'captured_at' => now(),
        ]);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte anexo',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Anexo']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);

        $this->assertArrayHasKey('appendix', $data);
        $this->assertNotEmpty($data['appendix']['summary']['rows']);
        $this->assertNotEmpty($data['appendix']['posts']['rows']);
        $this->assertSame(12.0, $data['appendix']['posts']['rows'][0]['link_clicks']);
    }

    public function test_assembler_includes_narrative(): void
    {
        [$workspace] = array_slice($this->workspaceContext(), 0, 1);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte narrativa',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Narrativa']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);

        $this->assertArrayHasKey('narrative', $data);
        $this->assertNotEmpty($data['narrative']['executive']['paragraphs']);
    }

    public function test_assembler_builds_channel_sections(): void
    {
        [$workspace, , $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-fb-top',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post Facebook top',
            'raw_metrics' => ['reach' => 500, 'likes' => 30],
            'captured_at' => now(),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'reel-fb-top',
            'post_type' => 'reel',
            'published_at' => now()->subDays(1),
            'content_preview' => 'Reel Facebook top',
            'raw_metrics' => ['reach' => 800, 'likes' => 50],
            'captured_at' => now(),
        ]);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte canal',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Por canal']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);

        $this->assertNotEmpty($data['channel_sections']);

        $facebook = collect($data['channel_sections'])->firstWhere('key', 'facebook');
        $this->assertNotNull($facebook);
        $this->assertSame('organic', $facebook['kind']);
        $this->assertCount(1, $facebook['top_posts']);
        $this->assertCount(1, $facebook['top_reels']);

        $metaAds = collect($data['channel_sections'])->firstWhere('key', 'meta_ads');
        $this->assertNotNull($metaAds);
        $this->assertSame('paid', $metaAds['kind']);
    }

    public function test_report_must_belong_to_workspace(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $otherWorkspace = Workspace::query()->create([
            'agency_id' => $workspace->agency_id,
            'name' => 'Otro workspace',
            'timezone' => 'UTC',
        ]);

        $report = Report::query()->create([
            'workspace_id' => $otherWorkspace->id,
            'name' => 'Reporte ajeno',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Ajeno']),
            'status' => ReportStatus::Ready,
            'file_path' => 'reports/99/1.pdf',
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.reports.show', [$workspace, $report]))
            ->assertNotFound();
    }

    public function test_pdf_template_uses_deck_structure(): void
    {
        [$workspace] = array_slice($this->workspaceContext(), 0, 1);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte deck',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Deck demo']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);
        $html = view('reports::pdf.report', $data)->render();

        $this->assertStringContainsString('class="deck"', $html);
        $this->assertStringContainsString('slide-cover', $html);
        $this->assertStringContainsString('slide-divider', $html);
        $this->assertStringContainsString('content-slide', $html);
    }

    public function test_appendix_csv_download(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-csv',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post CSV',
            'raw_metrics' => ['reach' => 200, 'impressions' => 250],
            'captured_at' => now(),
        ]);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte CSV',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'CSV']),
            'status' => ReportStatus::Ready,
            'file_path' => 'reports/'.$workspace->id.'/csv.pdf',
            'generated_at' => now(),
        ]);

        Storage::disk('local')->put($report->file_path, '%PDF-1.4 fake');

        $response = $this->actingAs($admin)
            ->get(route('workspaces.reports.appendix', [$workspace, $report]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('# Resumen general', $content);
        $this->assertStringContainsString('Post CSV', $content);
    }

    public function test_csv_exporter_builds_sections(): void
    {
        $csv = app(ReportAppendixCsvExporter::class)->export([
            'summary' => [
                'columns' => ['Métrica', 'Actual', 'Anterior', 'Cambio'],
                'rows' => [[
                    'label' => 'Alcance',
                    'current' => '1000',
                    'previous' => '800',
                    'change' => '+25%',
                ]],
            ],
        ]);

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('# Resumen general', $csv);
        $this->assertStringContainsString('Alcance', $csv);
    }

    public function test_appendix_excel_download(): void
    {
        [$workspace, $admin, $fbAsset] = $this->workspaceContext();

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'post-xlsx',
            'post_type' => 'feed',
            'published_at' => now()->subDays(2),
            'content_preview' => 'Post Excel',
            'raw_metrics' => ['reach' => 200, 'impressions' => 250],
            'captured_at' => now(),
        ]);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte Excel',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Excel']),
            'status' => ReportStatus::Ready,
            'file_path' => 'reports/'.$workspace->id.'/excel.pdf',
            'generated_at' => now(),
        ]);

        Storage::disk('local')->put($report->file_path, '%PDF-1.4 fake');

        $response = $this->actingAs($admin)
            ->get(route('workspaces.reports.appendix.excel', [$workspace, $report]));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        );

        $content = $response->streamedContent();
        $this->assertStringStartsWith('PK', $content);
    }

    public function test_excel_exporter_builds_workbook(): void
    {
        $xlsx = app(ReportAppendixExcelExporter::class)->export([
            'summary' => [
                'columns' => ['Métrica', 'Actual', 'Anterior', 'Cambio'],
                'rows' => [[
                    'label' => 'Alcance',
                    'current' => '1000',
                    'previous' => '800',
                    'change' => '+25%',
                ]],
            ],
        ]);

        $this->assertStringStartsWith('PK', $xlsx);
        $this->assertGreaterThan(1000, strlen($xlsx));
    }

    public function test_organic_meta_summary_when_fb_and_ig_connected(): void
    {
        [$workspace, , $fbAsset] = $this->workspaceContext();

        $connection = PlatformConnection::query()->first();

        $igAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::InstagramAccount,
            'platform_asset_id' => 'ig-report',
            'name' => '@marca',
            'is_active' => true,
        ]);

        OrganicPost::query()->create([
            'asset_id' => $fbAsset->id,
            'platform_post_id' => 'fb-meta-summary',
            'post_type' => 'feed',
            'published_at' => now()->subDay(),
            'raw_metrics' => ['reach' => 100, 'engagement' => 10],
            'captured_at' => now(),
        ]);

        OrganicPost::query()->create([
            'asset_id' => $igAsset->id,
            'platform_post_id' => 'ig-meta-summary',
            'post_type' => 'reel',
            'published_at' => now()->subDay(),
            'content_preview' => 'Reel destacado IG',
            'raw_metrics' => ['reach' => 400, 'engagement' => 40],
            'captured_at' => now(),
        ]);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte meta integrado',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Meta integrado']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);

        $this->assertNotNull($data['organic_meta_summary']);
        $this->assertSame('Facebook', $data['organic_meta_summary']['comparison']['left_label']);
        $this->assertSame('Instagram', $data['organic_meta_summary']['comparison']['right_label']);
        $this->assertNotEmpty($data['organic_meta_summary']['narrative']['paragraphs']);
        $this->assertStringContainsString('ecosistema Meta orgánico', $data['organic_meta_summary']['narrative']['paragraphs'][0]);

        $html = view('reports::pdf.report', $data)->render();
        $this->assertStringContainsString('meta-summary-block', $html);
        $this->assertStringContainsString('Qué nos dicen Facebook e Instagram juntos', $html);
    }

    public function test_organic_meta_summary_null_with_single_channel(): void
    {
        [$workspace] = array_slice($this->workspaceContext(), 0, 1);

        $report = Report::query()->create([
            'workspace_id' => $workspace->id,
            'name' => 'Reporte solo FB',
            'period_start' => now()->subDays(30)->toDateString(),
            'period_end' => now()->toDateString(),
            'config' => ReportConfig::normalize(['title' => 'Solo FB']),
            'status' => ReportStatus::Pending,
        ]);

        $data = app(ReportDataAssembler::class)->assemble($report, $workspace);

        $this->assertNull($data['organic_meta_summary']);
    }

    /**
     * @return array{0: Workspace, 1: User, 2: ConnectedAsset, 3: ConnectedAsset}
     */
    private function workspaceContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Reportes',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Reportes',
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
            'platform_asset_id' => 'page-report',
            'name' => 'Facebook',
            'is_active' => true,
        ]);

        $adsAsset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::MetaAds,
            'platform_asset_id' => 'act-report',
            'name' => 'Ads',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $fbAsset, $adsAsset];
    }
}
