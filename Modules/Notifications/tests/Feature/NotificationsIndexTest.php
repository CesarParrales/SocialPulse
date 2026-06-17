<?php

namespace Modules\Notifications\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Notifications\Notifications\IngestionFailedNotification;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class NotificationsIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->withoutVite();
    }

    public function test_user_can_view_notifications_index(): void
    {
        $user = $this->userWithNotification();

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Notifications/Index')
                ->has('notifications.data', 1)
                ->where('unread_count', 1)
            );
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = $this->userWithNotification();
        $notificationId = $user->unreadNotifications->first()->id;

        $this->actingAs($user)
            ->patch(route('notifications.read', $notificationId))
            ->assertRedirect();

        $this->assertNotNull(
            DatabaseNotification::query()->find($notificationId)?->read_at,
        );
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = $this->userWithNotification();

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(0, $user->fresh()->unreadNotifications()->count());
    }

    private function userWithNotification(): User
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia UI',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente UI',
            'timezone' => 'UTC',
        ]);

        $user = User::factory()->create(['agency_id' => $agency->id]);
        $user->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'status' => ConnectionStatus::Active,
        ]);

        $asset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-ui',
            'name' => 'Página UI',
            'is_active' => true,
        ]);

        $user->notify(new IngestionFailedNotification(
            asset: $asset,
            jobClass: 'OrganicFacebookJob',
            errorMessage: 'Test error',
        ));

        return $user->fresh();
    }
}
