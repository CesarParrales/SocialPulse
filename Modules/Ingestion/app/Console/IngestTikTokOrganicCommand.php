<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchOrganicTikTokDailyJob;
use Modules\Ingestion\Jobs\OrganicTikTokJob;
use Modules\Ingestion\Services\OrganicTikTokIngestionService;

class IngestTikTokOrganicCommand extends Command
{
    protected $signature = 'ingestion:tiktok-organic
                            {--asset= : Connected asset ID to ingest}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest organic TikTok videos and metrics for connected accounts';

    public function handle(OrganicTikTokIngestionService $service): int
    {
        $assetId = $this->option('asset');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::TikTokAccount)
                ->first();

            if ($asset === null) {
                $this->error("TikTok account asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->ingestAsset($asset);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            OrganicTikTokJob::dispatch($asset->id);
            $this->info("Dispatched OrganicTikTokJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::TikTokAccount)
                ->each(function (ConnectedAsset $asset) use ($service, &$count): void {
                    $log = $service->ingestAsset($asset);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->status->value} ({$log->records_ingested} records)");
                });

            $this->info("Processed {$count} TikTok account(s).");

            return self::SUCCESS;
        }

        DispatchOrganicTikTokDailyJob::dispatch();
        $this->info('Dispatched daily organic TikTok ingestion jobs.');

        return self::SUCCESS;
    }
}
