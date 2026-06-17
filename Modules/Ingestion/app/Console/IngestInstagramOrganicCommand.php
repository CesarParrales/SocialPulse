<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchOrganicInstagramDailyJob;
use Modules\Ingestion\Jobs\OrganicInstagramJob;
use Modules\Ingestion\Services\OrganicInstagramIngestionService;

class IngestInstagramOrganicCommand extends Command
{
    protected $signature = 'ingestion:instagram-organic
                            {--asset= : Connected asset ID to ingest}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest organic Instagram posts, reels and account metrics';

    public function handle(OrganicInstagramIngestionService $service): int
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
                $log = $service->ingestAsset($asset);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            OrganicInstagramJob::dispatch($asset->id);
            $this->info("Dispatched OrganicInstagramJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::InstagramAccount)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->ingestAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} records)");
                });

            $this->info("Processed {$count} Instagram account(s).");

            return self::SUCCESS;
        }

        DispatchOrganicInstagramDailyJob::dispatch();
        $this->info('Dispatched daily organic Instagram ingestion jobs.');

        return self::SUCCESS;
    }
}
