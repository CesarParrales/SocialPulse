<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class DashboardKpiPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_dashboard_exposes_kpi_preferences_from_workspace_settings(): void
    {
        [$workspace, $admin] = $this->workspaceContext([
            'dashboard' => [
                'visible_kpis' => ['reach', 'spend'],
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('workspaces.dashboard', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('kpiPreferences.visible_kpis', ['reach', 'spend'])
                ->where('kpiPreferences.can_customize', true)
            );
    }

    public function test_operator_can_update_workspace_kpi_preferences(): void
    {
        [$workspace, , $operator] = $this->workspaceContext();

        $this->actingAs($operator)
            ->put(route('workspaces.dashboard.kpi-preferences', $workspace), [
                'visible_kpis' => ['reach', 'impressions', 'spend'],
            ])
            ->assertRedirect();

        $workspace->refresh();

        $this->assertSame(
            ['reach', 'impressions', 'spend'],
            $workspace->settings['dashboard']['visible_kpis'],
        );
    }

    public function test_client_readonly_cannot_update_kpi_preferences(): void
    {
        [$workspace, , , $client] = $this->workspaceContext();

        $this->actingAs($client)
            ->put(route('workspaces.dashboard.kpi-preferences', $workspace), [
                'visible_kpis' => ['reach'],
            ])
            ->assertRedirect($client->clientHomeUrl());

        $workspace->refresh();

        $this->assertNull($workspace->settings);
    }

    public function test_kpi_preferences_require_at_least_one_metric(): void
    {
        [$workspace, $admin] = $this->workspaceContext();

        $this->actingAs($admin)
            ->put(route('workspaces.dashboard.kpi-preferences', $workspace), [
                'visible_kpis' => [],
            ])
            ->assertSessionHasErrors(['visible_kpis']);
    }

    /**
     * @param  array<string, mixed>|null  $settings
     * @return array{0: Workspace, 1: User, 2: User, 3: User}
     */
    private function workspaceContext(?array $settings = null): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia KPI',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca KPI',
            'timezone' => 'UTC',
            'settings' => $settings,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);
        $operator->workspaces()->attach($workspace->id, [
            'role' => WorkspaceMemberRole::Operator->value,
        ]);

        $client = User::factory()->create(['agency_id' => $agency->id]);
        $client->assignRole(SystemRole::ClientReadonly->value);
        $client->workspaces()->attach($workspace->id, [
            'role' => WorkspaceMemberRole::ClientReadonly->value,
        ]);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'status' => ConnectionStatus::Active,
        ]);

        ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-kpi',
            'name' => 'Página KPI',
            'is_active' => true,
        ]);

        return [$workspace, $admin, $operator, $client];
    }
}
