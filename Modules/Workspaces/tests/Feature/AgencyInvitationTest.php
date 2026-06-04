<?php

namespace Modules\Workspaces\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Mail\AgencyInvitationMail;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\AgencyInvitation;
use Tests\TestCase;

class AgencyInvitationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_agency_admin_can_invite_operator_by_email(): void
    {
        Mail::fake();

        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->post(route('team.invitations.store'), [
                'email' => 'nuevo@agencia.test',
                'role' => SystemRole::Operator->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('agency_invitations', [
            'agency_id' => $agency->id,
            'email' => 'nuevo@agencia.test',
        ]);

        Mail::assertSent(AgencyInvitationMail::class);
    }

    public function test_guest_can_accept_invitation_and_join_agency(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Test',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $invitation = AgencyInvitation::createForAgency(
            $agency,
            $admin,
            'nuevo@agencia.test',
            SystemRole::Operator,
        );

        $this->post(route('invitations.store', $invitation->token), [
            'name' => 'Nuevo Operador',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@agencia.test',
            'agency_id' => $agency->id,
        ]);

        $user = User::query()->where('email', 'nuevo@agencia.test')->first();
        $this->assertTrue($user->hasRole(SystemRole::Operator->value));
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }
}
