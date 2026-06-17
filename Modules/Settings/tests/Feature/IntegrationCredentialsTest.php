<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class IntegrationCredentialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();

        Config::set('connections.meta.app_id', 'env-meta-id');
        Config::set('connections.meta.app_secret', 'env-meta-secret');
        Config::set('connections.google.client_id', 'env-google-id');
        Config::set('connections.google.client_secret', 'env-google-secret');
        Config::set('connections.google.developer_token', 'env-dev-token');
    }

    public function test_resolver_prefers_agency_over_platform_and_env(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Resolver',
            'plan' => AgencyPlan::Agency,
        ]);

        IntegrationCredentialSet::platform()->update([
            'meta_app_id' => 'platform-meta-id',
            'meta_app_secret' => 'platform-meta-secret',
        ]);

        IntegrationCredentialSet::forAgency($agency->id)->update([
            'meta_app_id' => 'agency-meta-id',
            'meta_app_secret' => 'agency-meta-secret',
        ]);

        $resolver = app(IntegrationConfigResolver::class);

        $this->assertSame('agency-meta-id', $resolver->meta($agency->id)['app_id']);
        $this->assertSame('agency', $resolver->metaOAuthSource($agency->id));
    }

    public function test_resolver_falls_back_to_platform_then_env(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Fallback',
            'plan' => AgencyPlan::Agency,
        ]);

        IntegrationCredentialSet::platform()->update([
            'google_client_id' => 'platform-google-id',
            'google_client_secret' => 'platform-google-secret',
            'google_developer_token' => 'platform-dev-token',
        ]);

        $resolver = app(IntegrationConfigResolver::class);

        $this->assertSame('platform-google-id', $resolver->google($agency->id)['client_id']);
        $this->assertSame('platform', $resolver->googleSource($agency->id));

        IntegrationCredentialSet::platform()->delete();

        $this->assertSame('env-google-id', $resolver->google($agency->id)['client_id']);
        $this->assertSame('env', $resolver->googleSource($agency->id));
    }

    public function test_agency_admin_can_update_integration_credentials(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Credenciales',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->put(route('settings.agency.integrations.update'), [
                'meta_app_id' => 'panel-meta-id',
                'meta_app_secret' => 'panel-meta-secret',
                'google_client_id' => 'panel-google-id',
                'google_client_secret' => 'panel-google-secret',
                'google_developer_token' => 'panel-dev-token',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $credentials = IntegrationCredentialSet::forAgency($agency->id)->fresh();

        $this->assertSame('panel-meta-id', $credentials->meta_app_id);
        $this->assertSame('panel-meta-secret', $credentials->meta_app_secret);
        $this->assertSame('panel-google-id', $credentials->google_client_id);
    }

    public function test_super_admin_can_update_platform_integration_credentials(): void
    {
        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->actingAs($superAdmin)
            ->put(route('settings.platform.integrations.update'), [
                'meta_app_id' => 'global-meta-id',
                'meta_app_secret' => 'global-meta-secret',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $credentials = IntegrationCredentialSet::platform()->fresh();

        $this->assertSame('global-meta-id', $credentials->meta_app_id);
        $this->assertSame('global-meta-secret', $credentials->meta_app_secret);
    }

    public function test_super_admin_can_update_agency_integration_credentials_from_platform(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Platform Admin',
            'plan' => AgencyPlan::Agency,
        ]);

        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->actingAs($superAdmin)
            ->put(route('settings.platform.agencies.integrations.update', $agency), [
                'meta_app_id' => 'agency-from-platform-id',
                'meta_app_secret' => 'agency-from-platform-secret',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $credentials = IntegrationCredentialSet::forAgency($agency->id)->fresh();

        $this->assertSame('agency-from-platform-id', $credentials->meta_app_id);
        $this->assertSame('agency-from-platform-secret', $credentials->meta_app_secret);
    }

    public function test_operator_cannot_update_agency_integration_credentials(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Operador',
            'plan' => AgencyPlan::Agency,
        ]);

        $operator = User::factory()->create(['agency_id' => $agency->id]);
        $operator->assignRole(SystemRole::Operator->value);

        $this->actingAs($operator)
            ->put(route('settings.agency.integrations.update'), [
                'meta_app_id' => 'blocked',
            ])
            ->assertForbidden();
    }
}
