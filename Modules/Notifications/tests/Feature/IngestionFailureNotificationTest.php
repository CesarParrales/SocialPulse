<?php

namespace Modules\Notifications\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Ingestion\Jobs\OrganicFacebookJob;
use Modules\Notifications\Listeners\NotifyAgencyAdminsOnIngestionFailure;
use Modules\Notifications\Notifications\IngestionFailedNotification;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Tests\TestCase;

class IngestionFailureNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_agency_admin_is_notified_when_ingestion_job_fails(): void
    {
        Notification::fake();

        [$asset, $admin] = $this->facebookAssetContext();

        $job = new OrganicFacebookJob($asset->id);
        $payload = [
            'data' => [
                'command' => serialize($job),
            ],
        ];

        $failedJob = new class($payload)
        {
            public function __construct(private array $payload) {}

            public function getRawBody(): string
            {
                return json_encode($this->payload);
            }
        };

        $listener = app(NotifyAgencyAdminsOnIngestionFailure::class);
        $listener->handle(new JobFailed(
            connectionName: 'redis',
            job: $failedJob,
            exception: new \RuntimeException('Meta Graph API error: rate limit'),
        ));

        Notification::assertSentTo(
            $admin,
            IngestionFailedNotification::class,
            fn (IngestionFailedNotification $notification) => $notification->asset->is($asset)
                && str_contains($notification->errorMessage, 'rate limit'),
        );
    }

    /**
     * @return array{0: ConnectedAsset, 1: User}
     */
    private function facebookAssetContext(): array
    {
        $agency = Agency::query()->create([
            'name' => 'Agencia Notify',
            'plan' => AgencyPlan::Agency,
        ]);

        $workspace = Workspace::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Cliente Notify',
            'timezone' => 'UTC',
        ]);

        $admin = User::factory()->create(['agency_id' => $agency->id]);
        $admin->assignRole(SystemRole::AgencyAdmin->value);

        $connection = PlatformConnection::query()->create([
            'workspace_id' => $workspace->id,
            'platform' => Platform::Meta,
            'access_token' => 'token',
            'status' => ConnectionStatus::Active,
        ]);

        $asset = ConnectedAsset::query()->create([
            'connection_id' => $connection->id,
            'asset_type' => AssetType::FacebookPage,
            'platform_asset_id' => 'page-notify',
            'name' => 'Página Notify',
            'is_active' => true,
            'metadata' => ['page_access_token' => 'page-token'],
        ]);

        return [$asset, $admin];
    }
}
