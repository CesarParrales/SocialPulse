<?php

namespace Modules\Workspaces\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_agency_admin_can_create_workspace(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $response = $this->actingAs($admin)->post(route('workspaces.store'), [
            'name' => 'Cliente Nuevo',
            'industry_category' => 'Retail',
            'timezone' => 'America/Guayaquil',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('workspaces', [
            'agency_id' => $agency->id,
            'name' => 'Cliente Nuevo',
        ]);
    }

    public function test_operator_cannot_create_workspace(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->post(route('workspaces.store'), [
                'name' => 'Cliente Nuevo',
                'timezone' => 'UTC',
            ])
            ->assertForbidden();
    }

    public function test_operator_sees_only_assigned_workspaces_in_index(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $assigned = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Asignado',
            'timezone' => 'UTC',
        ]);

        Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Otro',
            'timezone' => 'UTC',
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);
        $operator->workspaces()->attach($assigned);

        $this->actingAs($operator)
            ->get(route('workspaces.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Workspaces/Index')
                ->has('workspaces', 1)
                ->where('workspaces.0.name', 'Asignado'));
    }
}
