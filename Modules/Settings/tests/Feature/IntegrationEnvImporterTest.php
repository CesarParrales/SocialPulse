<?php

namespace Modules\Settings\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\IntegrationEnvImporter;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Tests\TestCase;

class IntegrationEnvImporterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_importer_copies_env_values_to_platform_credentials(): void
    {
        Config::set('connections.meta.app_id', 'env-meta-id');
        Config::set('connections.meta.app_secret', 'env-meta-secret');

        $importer = app(IntegrationEnvImporter::class);
        $result = $importer->importPlatform();

        $this->assertContains('meta_app_id', $result['imported']);
        $this->assertContains('meta_app_secret', $result['imported']);

        $credentials = IntegrationCredentialSet::platform()->fresh();
        $this->assertSame('env-meta-id', $credentials->meta_app_id);
        $this->assertSame('env-meta-secret', $credentials->meta_app_secret);
    }

    public function test_importer_skips_existing_values_without_force(): void
    {
        Config::set('connections.meta.app_id', 'env-meta-id');

        IntegrationCredentialSet::platform()->update([
            'meta_app_id' => 'existing-id',
        ]);

        $importer = app(IntegrationEnvImporter::class);
        $result = $importer->importPlatform();

        $this->assertNotContains('meta_app_id', $result['imported']);
        $this->assertSame('existing-id', IntegrationCredentialSet::platform()->fresh()->meta_app_id);
    }

    public function test_super_admin_can_import_platform_credentials_via_http(): void
    {
        Config::set('connections.tiktok.client_key', 'env-tiktok-key');
        Config::set('connections.tiktok.client_secret', 'env-tiktok-secret');

        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->actingAs($superAdmin)
            ->post(route('settings.platform.integrations.import-env'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $credentials = IntegrationCredentialSet::platform()->fresh();
        $this->assertSame('env-tiktok-key', $credentials->tiktok_client_key);
        $this->assertSame('env-tiktok-secret', $credentials->tiktok_client_secret);
    }

    public function test_super_admin_can_import_agency_credentials_via_http(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Import',
            'plan' => AgencyPlan::Agency,
        ]);

        Config::set('connections.linkedin.client_id', 'env-linkedin-id');
        Config::set('connections.linkedin.client_secret', 'env-linkedin-secret');

        $superAdmin = User::factory()->create(['agency_id' => null]);
        $superAdmin->assignRole(SystemRole::SuperAdmin->value);

        $this->actingAs($superAdmin)
            ->post(route('settings.platform.agencies.integrations.import-env', $agency))
            ->assertRedirect()
            ->assertSessionHas('success');

        $credentials = IntegrationCredentialSet::forAgency($agency->id)->fresh();
        $this->assertSame('env-linkedin-id', $credentials->linkedin_client_id);
    }

    public function test_agency_admin_cannot_import_from_env(): void
    {
        Config::set('connections.meta.app_id', 'env-meta-id');

        $agency = Agency::query()->create([
            'name' => 'Agencia',
            'plan' => AgencyPlan::Agency,
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $this->actingAs($admin)
            ->post(route('settings.platform.integrations.import-env'))
            ->assertForbidden();
    }
}
