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

class ClientReadonlyAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_client_is_redirected_from_home_to_workspace_dashboard(): void
    {
        [$workspace, $client] = $this->clientContext();

        $this->actingAs($client)
            ->get(route('dashboard'))
            ->assertRedirect(route('workspaces.dashboard', $workspace));
    }

    public function test_client_can_view_assigned_workspace_dashboard(): void
    {
        [$workspace, $client] = $this->clientContext();

        $this->actingAs($client)
            ->get(route('workspaces.dashboard', $workspace))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Dashboard/Workspace'));
    }

    public function test_client_cannot_access_connections(): void
    {
        [$workspace, $client] = $this->clientContext();

        $this->actingAs($client)
            ->get(route('workspaces.connections.index', $workspace))
            ->assertRedirect(route('workspaces.dashboard', $workspace));
    }

    public function test_client_cannot_access_team_or_settings(): void
    {
        [, $client] = $this->clientContext();

        $this->actingAs($client)
            ->get(route('team.index'))
            ->assertRedirect();

        $this->actingAs($client)
            ->get(route('settings.agency.edit'))
            ->assertRedirect();
    }

    public function test_assigning_client_role_syncs_system_role(): void
    {
        [$workspace, $admin, $member] = $this->adminAndMemberContext();

        $this->actingAs($admin)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => $member->email,
                'role' => WorkspaceMemberRole::ClientReadonly->value,
            ])
            ->assertRedirect();

        $member->refresh();

        $this->assertTrue($member->isClientReadonly());
        $this->assertSame(
            WorkspaceMemberRole::ClientReadonly,
            $member->workspaceMemberRole($workspace),
        );
    }

    public function test_client_only_sees_assigned_workspaces(): void
    {
        [$workspaceA, $client] = $this->clientContext();

        $workspaceB = Workspace::query()->create([
            'agency_id' => $workspaceA->agency_id,
            'name' => 'Otra Marca',
            'timezone' => 'UTC',
        ]);

        $this->actingAs($client)
            ->get(route('workspaces.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('workspaces', 1)
                ->where('workspaces.0.id', $workspaceA->id)
            );

        $this->assertFalse($client->canAccessWorkspace($workspaceB));
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function clientContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Client',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Cliente',
            'timezone' => 'UTC',
        ]);

        $client = User::factory()->create(['agency_id' => $agency->id]);
        $client->assignRole(SystemRole::ClientReadonly->value);
        $client->workspaces()->attach($workspace->id, [
            'role' => WorkspaceMemberRole::ClientReadonly->value,
        ]);

        return [$workspace, $client];
    }

    /**
     * @return array{0: Workspace, 1: User, 2: User}
     */
    private function adminAndMemberContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Assign',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Assign',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $member = User::factory()->create(['agency_id' => $agency->id]);

        return [$workspace, $admin, $member];
    }
}
