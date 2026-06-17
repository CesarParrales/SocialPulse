<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchOrganicYouTubeDailyJob;
use Modules\Ingestion\Jobs\OrganicYouTubeJob;
use Modules\Ingestion\Services\OrganicYouTubeIngestionService;

class IngestYouTubeOrganicCommand extends Command
{
    protected $signature = 'ingestion:youtube-organic
                            {--asset= : Connected asset ID to ingest}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest organic YouTube videos and metrics for connected channels';

    public function handle(OrganicYouTubeIngestionService $service): int
    {
        $assetId = $this->option('asset');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::YouTubeChannel)
                ->first();

            if ($asset === null) {
                $this->error("YouTube channel asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->ingestAsset($asset);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            OrganicYouTubeJob::dispatch($asset->id);
            $this->info("Dispatched OrganicYouTubeJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::YouTubeChannel)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->ingestAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} records)");
                });

            $this->info("Processed {$count} YouTube channel(s).");

            return self::SUCCESS;
        }

        DispatchOrganicYouTubeDailyJob::dispatch();
        $this->info('Dispatched daily organic YouTube ingestion jobs.');

        return self::SUCCESS;
    }
}
