<?php

namespace Modules\Workspaces\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Mail\AgencyInvitationMail;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\AgencyInvitation;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class WorkspaceClientInviteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
        Mail::fake();
    }

    public function test_admin_can_invite_client_from_workspace_overview(): void
    {
        [$workspace, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => 'nuevo.cliente@empresa.test',
                'role' => WorkspaceMemberRole::ClientReadonly->value,
                'invite' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $invitation = AgencyInvitation::query()
            ->where('email', 'nuevo.cliente@empresa.test')
            ->first();

        $this->assertNotNull($invitation);
        $this->assertSame(SystemRole::ClientReadonly, $invitation->role);
        $this->assertSame($workspace->id, $invitation->workspace_id);

        Mail::assertSent(AgencyInvitationMail::class, fn (AgencyInvitationMail $mail) => $mail->hasTo('nuevo.cliente@empresa.test'));
    }

    public function test_accepting_workspace_invitation_auto_assigns_client(): void
    {
        [$workspace, $admin] = $this->adminContext();

        $invitation = AgencyInvitation::createForAgency(
            $workspace->agency,
            $admin,
            'cliente.auto@empresa.test',
            SystemRole::ClientReadonly,
            $workspace,
        );

        $this->post(route('invitations.store', $invitation->token), [
            'name' => 'Cliente Auto',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('workspaces.dashboard', $workspace));

        $user = User::query()->where('email', 'cliente.auto@empresa.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->isClientReadonly());
        $this->assertTrue($user->canAccessWorkspace($workspace));
    }

    public function test_missing_user_without_invite_flag_returns_validation_error(): void
    {
        [$workspace, $admin] = $this->adminContext();

        $this->actingAs($admin)
            ->post(route('workspaces.members.store', $workspace), [
                'email' => 'desconocido@empresa.test',
                'role' => WorkspaceMemberRole::ClientReadonly->value,
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('email');
    }

    /**
     * @return array{0: Workspace, 1: User}
     */
    private function adminContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Invite',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Marca Invite',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$workspace, $admin];
    }
}
