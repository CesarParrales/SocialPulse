<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_user_can_switch_locale(): void
    {
        $user = $this->agencyAdmin();

        $this->actingAs($user)
            ->from(route('dashboard'))
            ->patch(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect(route('dashboard'));

        $this->assertSame('en', $user->fresh()->locale);
    }

    public function test_dashboard_shares_translations_for_locale(): void
    {
        $user = $this->agencyAdmin();
        $user->update(['locale' => 'en']);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'en')
                ->where('translations.nav.home', 'Home')
            );
    }

    public function test_guest_can_switch_locale_via_session(): void
    {
        $this->from(route('login'))
            ->patch(route('locale.update'), ['locale' => 'en'])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'en')
                ->where('translations.auth.login', 'Log in')
            );
    }

    private function agencyAdmin(): User
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Locale',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create([
            'agency_id' => $agency->id,
            'locale' => 'es',
        ]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        return $admin;
    }
}
