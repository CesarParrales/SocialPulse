<?php

namespace Modules\Notifications\Listeners;

use App\Models\User;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Notification;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\OrganicFacebookJob;
use Modules\Ingestion\Jobs\OrganicInstagramJob;
use Modules\Ingestion\Jobs\PaidGoogleJob;
use Modules\Ingestion\Jobs\PaidMetaJob;
use Modules\Ingestion\Jobs\StoriesWatcherJob;
use Modules\Notifications\Notifications\IngestionFailedNotification;
use Modules\Workspaces\Enums\SystemRole;

class NotifyAgencyAdminsOnIngestionFailure
{
    /** @var list<class-string> */
    private const INGESTION_JOBS = [
        OrganicFacebookJob::class,
        OrganicInstagramJob::class,
        StoriesWatcherJob::class,
        PaidMetaJob::class,
        PaidGoogleJob::class,
    ];

    public function handle(JobFailed $event): void
    {
        $command = $this->resolveCommand($event);

        if ($command === null) {
            return;
        }

        $asset = ConnectedAsset::query()
            ->with('connection.workspace')
            ->find($command->assetId);

        if ($asset?->connection?->workspace === null) {
            return;
        }

        $agencyId = $asset->connection->workspace->agency_id;

        $admins = User::role(SystemRole::AgencyAdmin->value)
            ->where('agency_id', $agencyId)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notification::send($admins, new IngestionFailedNotification(
            asset: $asset,
            jobClass: $command::class,
            errorMessage: $event->exception->getMessage(),
        ));
    }

    private function resolveCommand(JobFailed $event): OrganicFacebookJob|OrganicInstagramJob|StoriesWatcherJob|PaidMetaJob|PaidGoogleJob|null
    {
        $payload = json_decode($event->job->getRawBody(), true);
        $command = unserialize($payload['data']['command'] ?? '');

        foreach (self::INGESTION_JOBS as $jobClass) {
            if ($command instanceof $jobClass) {
                return $command;
            }
        }

        return null;
    }
}
