<?php

namespace Modules\Notifications\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Notifications\Jobs\DispatchTokenExpiryWarningsJob;
use Modules\Notifications\Notifications\ConnectionTokenExpiringNotification;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class TokenExpiryWarningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_agency_admin_is_warned_when_token_expires_soon(): void
    {
        Notification::fake();

        [$connection, $admin] = $this->expiringConnectionContext();

        (new DispatchTokenExpiryWarningsJob)->handle();

        Notification::assertSentTo(
            $admin,
            ConnectionTokenExpiringNotification::class,
            fn (ConnectionTokenExpiringNotification $notification) => $notification->platformConnection->is($connection),
        );

        $connection->refresh();

        $this->assertNotNull($connection->metadata['expiry_warning_for'] ?? null);
    }

    public function test_expiry_warning_is_not_sent_twice_for_same_expiry(): void
    {
        Notification::fake();

        [$connection] = $this->expiringConnectionContext();

        (new DispatchTokenExpiryWarningsJob)->handle();
        (new DispatchTokenExpiryWarningsJob)->handle();

        Notification::assertSentTimes(ConnectionTokenExpiringNotification::class, 1);
    }

    /**
     * @return array{0: PlatformConnection, 1: User}
     */
    private function expiringConnectionContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Expiry',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente Expiry',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'token_expires_at' => now()->addDays(3),
            'status' => ConnectionStatus::Active,
        ]);

        return [$connection, $admin];
    }
}
