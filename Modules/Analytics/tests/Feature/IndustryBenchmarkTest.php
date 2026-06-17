<?php

namespace Modules\Analytics\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Analytics\Models\BenchmarkSnapshot;
use Modules\Analytics\Models\IndustryBenchmarkSegment;
use Modules\Analytics\Services\IndustryBenchmarkAggregatorService;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class IndustryBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_aggregator_builds_segments_from_workspace_snapshots(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Bench Ind',
            'plan' => AgencyPlan::Agency,
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $workspace = Workspace::query()->create([
                'agency_id' => $agency->id,
                'name' => "Marca {$i}",
                'timezone' => 'UTC',
                'industry_category' => 'retail',
                'region' => 'LATAM',
            ]);

            BenchmarkSnapshot::query()->create([
                'workspace_id' => $workspace->id,
                'asset_id' => null,
                'period_start' => now()->subDays(90)->toDateString(),
                'period_end' => now()->subDay()->toDateString(),
                'engagement_rate_avg' => 2.0 + $i,
                'reach_avg' => 100 * $i,
                'cpm_avg' => 10.0,
                'calculated_at' => now(),
            ]);
        }

        $written = app(IndustryBenchmarkAggregatorService::class)->aggregate();

        $this->assertSame(1, $written);

        $this->assertDatabaseHas('industry_benchmark_segments', [
            'industry_category' => 'retail',
            'region' => 'LATAM',
            'sample_size' => 3,
        ]);
    }

    public function test_industry_benchmark_hidden_when_sample_below_threshold(): void
    {
        [$workspace, $admin] = $this->workspaceWithIndustrySegment(sampleSize: 5);

        $this->actingAs($admin)
            ->get(route('workspaces.benchmarks', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('benchmarks.industry_benchmark_available', false)
                ->where('benchmarks.industry_sample_size', 5)
            );
    }

    public function test_industry_benchmark_shown_when_sample_meets_threshold(): void
    {
        [$workspace, $admin] = $this->workspaceWithIndustrySegment(sampleSize: 35);

        $this->actingAs($admin)
            ->get(route('workspaces.benchmarks', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('benchmarks.industry_benchmark_available', true)
                ->where('benchmarks.industry_sample_size', 35)
                ->has('benchmarks.metrics.engagement_rate.industry')
            );
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function workspaceWithIndustrySegment(int $sampleSize): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Segmento',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Segmento',
            'timezone' => 'UTC',
            'industry_category' => 'retail',
            'region' => 'LATAM',
        ]);

        IndustryBenchmarkSegment::query()->create([
            'industry_category' => 'retail',
            'community_size_band' => 'lt_10k',
            'region' => 'LATAM',
            'sample_size' => $sampleSize,
            'engagement_rate_avg' => 3.5,
            'reach_avg' => 250,
            'cpm_avg' => 12,
            'calculated_at' => now(),
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }
}
