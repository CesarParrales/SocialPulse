<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class SettingsIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_settings_url_shows_hub_for_agency_admin(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Redirect',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->get('/settings')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Settings/Index')
                ->has('agency')
                ->has('integrations')
                ->has('integrationsSummary')
                ->where('agency.name', 'Agencia Redirect')
            );
    }

    public function test_settings_url_redirects_super_admin_without_agency_to_platform_integrations(): void
    {
        $super = User::factory()->create(['agency_id' => null]);
        $super->assignRole(SystemRole::SuperAdmin->value);

        $this->actingAs($super)
            ->get('/settings')
            ->assertRedirect(route('settings.platform.index', ['tab' => 'integrations']));
    }

    public function test_operator_cannot_access_settings_index(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Op',
            'plan' => AgencyPlan::Agency,
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->get('/settings')
            ->assertForbidden();
    }
}
