<?php

namespace Modules\Connections\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\PlatformTokenRefreshService;

class DispatchTokenRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $withinDays = 7,
    ) {
        $this->onQueue('default');
    }

    public function handle(PlatformTokenRefreshService $refreshService): void
    {
        PlatformConnection::query()
            ->where('status', ConnectionStatus::Active)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '<=', now()->addDays($this->withinDays))
            ->orderBy('id')
            ->each(function (PlatformConnection $connection) use ($refreshService): void {
                if ($refreshService->needsRefresh($connection, $this->withinDays)) {
                    RefreshPlatformTokenJob::dispatch($connection->id);
                }
            });
    }
}
