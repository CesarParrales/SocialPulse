<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class HorizonAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_super_admin_can_access_horizon_in_production(): void
    {
        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->app->detectEnvironment(fn () => 'production');

        $this->actingAs($superAdmin)
            ->get('/horizon')
            ->assertOk();
    }

    public function test_agency_admin_cannot_access_horizon_in_production(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->app->detectEnvironment(fn () => 'production');

        $this->actingAs($admin)
            ->get('/horizon')
            ->assertForbidden();
    }
}
