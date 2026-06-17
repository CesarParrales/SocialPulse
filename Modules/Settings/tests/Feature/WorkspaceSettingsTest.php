<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_agency_admin_can_view_workspace_settings(): void
    {
        [$admin, $workspace] = $this->agencyAdminWithWorkspace();

        $this->actingAs($admin)
            ->get(route('settings.workspace.edit', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Workspace')
                ->where('workspace.id', $workspace->id)
                ->has('timezones')
                ->has('regions')
            );
    }

    public function test_agency_admin_can_update_workspace_settings(): void
    {
        [$admin, $workspace] = $this->agencyAdminWithWorkspace();

        $this->actingAs($admin)
            ->put(route('settings.workspace.update', $workspace), [
                'name' => 'Marca Actualizada',
                'industry_category' => 'Retail',
                'region' => 'LATAM',
                'timezone' => 'America/Bogota',
            ])
            ->assertRedirect();

        $workspace->refresh();

        $this->assertSame('Marca Actualizada', $workspace->name);
        $this->assertSame('Retail', $workspace->industry_category);
        $this->assertSame('LATAM', $workspace->region);
        $this->assertSame('America/Bogota', $workspace->timezone);
    }

    public function test_operator_cannot_access_workspace_settings(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'WS',
            'timezone' => 'UTC',
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->get(route('settings.workspace.edit', $workspace))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Workspace}
     */
    private function agencyAdminWithWorkspace(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia WS',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Demo',
            'timezone' => 'UTC',
        ]);

        return [$admin, $workspace];
    }
}
