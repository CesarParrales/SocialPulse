<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchOrganicLinkedInDailyJob;
use Modules\Ingestion\Jobs\OrganicLinkedInJob;
use Modules\Ingestion\Services\OrganicLinkedInIngestionService;

class IngestLinkedInOrganicCommand extends Command
{
    protected $signature = 'ingestion:linkedin-organic
                            {--asset= : Connected asset ID to ingest}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest organic LinkedIn posts and metrics for connected pages';

    public function handle(OrganicLinkedInIngestionService $service): int
    {
        $assetId = $this->option('asset');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::LinkedInPage)
                ->first();

            if ($asset === null) {
                $this->error("LinkedIn page asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->ingestAsset($asset);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            OrganicLinkedInJob::dispatch($asset->id);
            $this->info("Dispatched OrganicLinkedInJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::LinkedInPage)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->ingestAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} records)");
                });

            $this->info("Processed {$count} LinkedIn page(s).");

            return self::SUCCESS;
        }

        DispatchOrganicLinkedInDailyJob::dispatch();
        $this->info('Dispatched daily organic LinkedIn ingestion jobs.');

        return self::SUCCESS;
    }
}
