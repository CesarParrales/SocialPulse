<?php

namespace Modules\Workspaces\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_any_workspace(): void
    {
        $this->seed(RolesSeeder::class);

        $agency = Agency::query()->create([
            'name' => 'Agencia Demo',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente A',
            'timezone' => 'America/Guayaquil',
        ]);

        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->assertTrue($superAdmin->canAccessWorkspace($workspace));
    }

    public function test_operator_only_accesses_assigned_workspaces(): void
    {
        $this->seed(RolesSeeder::class);

        $agency = Agency::query()->create([
            'name' => 'Agencia Demo',
            'plan' => AgencyPlan::Agency,
        ]);

        $assigned = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente A',
        ]);

        $other = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente B',
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);
        $operator->workspaces()->attach($assigned->id, [
            'role' => WorkspaceMemberRole::Operator->value,
        ]);

        $this->assertTrue($operator->canAccessWorkspace($assigned));
        $this->assertFalse($operator->canAccessWorkspace($other));
    }

    public function test_agency_admin_accesses_all_agency_workspaces(): void
    {
        $this->seed(RolesSeeder::class);

        $agency = Agency::query()->create([
            'name' => 'Agencia Demo',
            'plan' => AgencyPlan::AgencyPro,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente A',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->assertTrue($admin->canAccessWorkspace($workspace));
    }
}
