<?php

namespace Modules\Connections\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\PlatformTokenRefreshService;
use Modules\Notifications\Notifications\ConnectionTokenRefreshFailedNotification;
use Modules\Workspaces\Enums\SystemRole;
use Throwable;

class RefreshPlatformTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $connectionId,
    ) {
        $this->onQueue('default');
    }

    public function handle(PlatformTokenRefreshService $refreshService): void
    {
        $connection = PlatformConnection::query()
            ->with('workspace')
            ->findOrFail($this->connectionId);

        if (! $refreshService->needsRefresh($connection)) {
            return;
        }

        $refreshService->refresh($connection);
    }

    public function failed(?Throwable $exception): void
    {
        $connection = PlatformConnection::query()
            ->with('workspace')
            ->find($this->connectionId);

        if ($connection === null) {
            return;
        }

        $connection->markExpired();

        $workspace = $connection->workspace;

        if ($workspace === null) {
            return;
        }

        $admins = User::role(SystemRole::AgencyAdmin->value)
            ->where('agency_id', $workspace->agency_id)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new ConnectionTokenRefreshFailedNotification(
            platformConnection: $connection,
            errorMessage: $exception?->getMessage() ?? 'Error desconocido al refrescar el token.',
        ));
    }
}
