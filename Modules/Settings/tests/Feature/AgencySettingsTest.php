<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class AgencySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_agency_admin_can_view_settings(): void
    {
        [$admin, $agency] = $this->agencyAdmin();

        $this->actingAs($admin)
            ->get(route('settings.agency.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Agency')
                ->where('agency.id', $agency->id)
                ->has('integrations.meta')
                ->has('integrations.google')
            );
    }

    public function test_agency_admin_can_update_agency_settings(): void
    {
        [$admin, $agency] = $this->agencyAdmin();

        $this->actingAs($admin)
            ->put(route('settings.agency.update'), [
                'name' => 'Agencia Renombrada',
                'billing_email' => 'billing@agencia.test',
                'default_locale' => 'en',
            ])
            ->assertRedirect();

        $agency->refresh();

        $this->assertSame('Agencia Renombrada', $agency->name);
        $this->assertSame('billing@agencia.test', $agency->billing_email);
        $this->assertSame('en', $agency->settings['default_locale']);
    }

    public function test_operator_cannot_access_settings(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia',
            'plan' => AgencyPlan::Agency,
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->get(route('settings.agency.edit'))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Agency}
     */
    private function agencyAdmin(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Config',
            'plan' => AgencyPlan::Agency,
            'billing_email' => 'old@agencia.test',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return [$admin, $agency];
    }
}
