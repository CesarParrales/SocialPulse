<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchOrganicFacebookDailyJob;
use Modules\Ingestion\Jobs\OrganicFacebookJob;
use Modules\Ingestion\Services\OrganicFacebookIngestionService;

class IngestFacebookOrganicCommand extends Command
{
    protected $signature = 'ingestion:facebook-organic
                            {--asset= : Connected asset ID to ingest}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest organic Facebook posts and page metrics for connected pages';

    public function handle(OrganicFacebookIngestionService $service): int
    {
        $assetId = $this->option('asset');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::FacebookPage)
                ->first();

            if ($asset === null) {
                $this->error("Facebook page asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->ingestAsset($asset);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            OrganicFacebookJob::dispatch($asset->id);
            $this->info("Dispatched OrganicFacebookJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::FacebookPage)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->ingestAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} records)");
                });

            $this->info("Processed {$count} Facebook page(s).");

            return self::SUCCESS;
        }

        DispatchOrganicFacebookDailyJob::dispatch();
        $this->info('Dispatched daily organic Facebook ingestion jobs.');

        return self::SUCCESS;
    }
}
