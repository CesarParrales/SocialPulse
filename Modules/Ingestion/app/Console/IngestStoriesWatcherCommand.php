<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchStoriesWatcherJob;
use Modules\Ingestion\Jobs\StoriesWatcherJob;
use Modules\Ingestion\Services\StoriesWatcherService;

class IngestStoriesWatcherCommand extends Command
{
    protected $signature = 'ingestion:stories-watcher
                            {--asset= : Connected asset ID to watch}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Capture active Instagram Stories metrics before they expire';

    public function handle(StoriesWatcherService $service): int
    {
        $assetId = $this->option('asset');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::InstagramAccount)
                ->first();

            if ($asset === null) {
                $this->error("Instagram asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->watchAsset($asset);
                $this->info("Watcher {$log->status->value}: {$log->records_ingested} stories in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            StoriesWatcherJob::dispatch($asset->id);
            $this->info("Dispatched StoriesWatcherJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::InstagramAccount)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->watchAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} stories)");
                });

            $this->info("Processed {$count} Instagram account(s).");

            return self::SUCCESS;
        }

        DispatchStoriesWatcherJob::dispatch();
        $this->info('Dispatched stories watcher jobs.');

        return self::SUCCESS;
    }
}
