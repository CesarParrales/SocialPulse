<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_guest_sees_inertia_404_page(): void
    {
        $this->get('/ruta-inexistente-socialpulse')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('Error')
                ->where('status', 404)
            );
    }

    public function test_authenticated_user_sees_inertia_404_page(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Error',
            'plan' => AgencyPlan::Agency,
        ]);

        $user = User::factory()->create(['agency_id' => $agency->id]);
        $user->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($user)
            ->get('/otra-ruta-inexistente')
            ->assertNotFound()
            ->assertInertia(fn ($page) => $page
                ->component('Error')
                ->where('status', 404)
            );
    }
}
