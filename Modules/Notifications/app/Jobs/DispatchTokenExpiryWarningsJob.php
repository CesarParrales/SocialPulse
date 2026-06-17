<?php

namespace Modules\Notifications\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\PlatformConnection;
use Modules\Notifications\Notifications\ConnectionTokenExpiringNotification;
use Modules\Workspaces\Enums\SystemRole;

class DispatchTokenExpiryWarningsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $withinDays = 7,
    ) {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        PlatformConnection::query()
            ->with('workspace')
            ->where('status', ConnectionStatus::Active)
            ->whereNotNull('token_expires_at')
            ->where('token_expires_at', '>', now())
            ->where('token_expires_at', '<=', now()->addDays($this->withinDays))
            ->orderBy('id')
            ->each(function (PlatformConnection $connection): void {
                $expiryKey = $connection->token_expires_at?->toIso8601String();

                if ($expiryKey === null) {
                    return;
                }

                $metadata = $connection->metadata ?? [];

                if (($metadata['expiry_warning_for'] ?? null) === $expiryKey) {
                    return;
                }

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

                Notification::send($admins, new ConnectionTokenExpiringNotification($connection));

                $connection->update([
                    'metadata' => array_merge($metadata, [
                        'expiry_warning_for' => $expiryKey,
                        'expiry_warning_sent_at' => now()->toIso8601String(),
                    ]),
                ]);
            });
    }
}
