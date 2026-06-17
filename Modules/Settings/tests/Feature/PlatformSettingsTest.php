<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_super_admin_can_view_platform_settings(): void
    {
        $superAdmin = $this->superAdmin();
        $agency = $this->createAgency('Agencia Test');

        $this->actingAs($superAdmin)
            ->get(route('settings.platform.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Platform')
                ->has('stats')
                ->has('integrations')
                ->where('agencies.0.id', $agency->id)
            );
    }

    public function test_super_admin_can_update_agency_from_platform(): void
    {
        $superAdmin = $this->superAdmin();
        $agency = $this->createAgency('Agencia Original');

        $this->actingAs($superAdmin)
            ->put(route('settings.platform.agencies.update', $agency), [
                'name' => 'Agencia Actualizada',
                'plan' => AgencyPlan::Enterprise->value,
                'billing_email' => 'billing@new.test',
            ])
            ->assertRedirect(route('settings.platform.agencies.edit', $agency));

        $agency->refresh();

        $this->assertSame('Agencia Actualizada', $agency->name);
        $this->assertSame(AgencyPlan::Enterprise, $agency->plan);
        $this->assertSame('billing@new.test', $agency->billing_email);
    }

    public function test_super_admin_can_create_agency(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->post(route('settings.platform.agencies.store'), [
                'name' => 'Nueva Agencia',
                'plan' => AgencyPlan::Starter->value,
                'billing_email' => 'billing@nueva.test',
            ])
            ->assertRedirect(route('settings.platform.index'));

        $this->assertDatabaseHas('agencies', [
            'name' => 'Nueva Agencia',
            'plan' => AgencyPlan::Starter->value,
            'billing_email' => 'billing@nueva.test',
        ]);
    }

    public function test_super_admin_can_create_agency_and_invite_admin(): void
    {
        $superAdmin = $this->superAdmin();

        $this->actingAs($superAdmin)
            ->post(route('settings.platform.agencies.store'), [
                'name' => 'Agencia Con Admin',
                'plan' => AgencyPlan::Agency->value,
                'admin_email' => 'admin@nueva-agencia.test',
            ])
            ->assertRedirect(route('settings.platform.index'))
            ->assertSessionHas('success');

        $agency = Agency::query()->where('name', 'Agencia Con Admin')->first();

        $this->assertNotNull($agency);
        $this->assertDatabaseHas('agency_invitations', [
            'agency_id' => $agency->id,
            'email' => 'admin@nueva-agencia.test',
            'role' => SystemRole::AgencyAdmin->value,
        ]);
    }

    public function test_super_admin_can_view_agency_integrations_tab(): void
    {
        $superAdmin = $this->superAdmin();
        $agency = $this->createAgency('Agencia Integraciones');

        $this->actingAs($superAdmin)
            ->get(route('settings.platform.agencies.edit', ['agency' => $agency, 'tab' => 'integrations']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/PlatformAgency')
                ->where('activeTab', 'integrations')
                ->where('agency.id', $agency->id)
                ->has('integrations.meta')
                ->has('integrationCredentials')
                ->has('oauthRedirects')
            );
    }

    public function test_agency_admin_cannot_access_platform_settings(): void
    {
        $agency = $this->createAgency('Agencia');
        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->get(route('settings.platform.index'))
            ->assertForbidden();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['agency_id' => null]);
        $user->assignRole(SystemRole::SuperAdmin->value);

        return $user;
    }

    private function createAgency(string $name): Agency
    {
        return Agency::query()->create([
            'name' => $name,
            'plan' => AgencyPlan::Agency,
        ]);
    }
}
