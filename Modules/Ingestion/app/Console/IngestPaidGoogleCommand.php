<?php

namespace Modules\Ingestion\Console;

use Illuminate\Console\Command;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Jobs\DispatchPaidGoogleDailyJob;
use Modules\Ingestion\Jobs\DispatchPaidGoogleIntradayJob;
use Modules\Ingestion\Jobs\PaidGoogleJob;
use Modules\Ingestion\Services\PaidGoogleIngestionService;

class IngestPaidGoogleCommand extends Command
{
    protected $signature = 'ingestion:paid-google
                            {--asset= : Connected Google Ads asset ID}
                            {--preliminary : Ingest today as preliminary data}
                            {--sync : Run synchronously instead of dispatching to the queue}';

    protected $description = 'Ingest paid Google Ads campaign performance';

    public function handle(PaidGoogleIngestionService $service): int
    {
        $assetId = $this->option('asset');
        $preliminary = (bool) $this->option('preliminary');
        $sync = (bool) $this->option('sync');

        if ($assetId !== null) {
            $asset = ConnectedAsset::query()
                ->whereKey($assetId)
                ->where('asset_type', AssetType::GoogleAds)
                ->first();

            if ($asset === null) {
                $this->error("Google Ads asset {$assetId} not found.");

                return self::FAILURE;
            }

            if ($sync) {
                $log = $service->ingestAsset($asset, $preliminary);
                $this->info("Ingestion {$log->status->value}: {$log->records_ingested} records in {$log->duration_ms}ms.");

                return $log->status->value === 'success' ? self::SUCCESS : self::FAILURE;
            }

            PaidGoogleJob::dispatch($asset->id, $preliminary);
            $this->info("Dispatched PaidGoogleJob for asset {$asset->id}.");

            return self::SUCCESS;
        }

        if ($sync) {
            $count = 0;

            ConnectedAsset::query()
                ->where('is_active', true)
                ->where('asset_type', AssetType::GoogleAds)
                ->each(function (ConnectedAsset $asset) use ($service, $preliminary, &$count): void {
                    $log = $service->ingestAsset($asset, $preliminary);
                    $count++;
                    $this->line("Asset {$asset->id}: {$log->records_ingested} records — {$log->status->value}");
                });

            $this->info("Processed {$count} Google Ads account(s).");

            return self::SUCCESS;
        }

        if ($preliminary) {
            DispatchPaidGoogleIntradayJob::dispatch();
            $this->info('Dispatched intraday paid Google ingestion jobs.');
        } else {
            DispatchPaidGoogleDailyJob::dispatch();
            $this->info('Dispatched daily paid Google ingestion jobs.');
        }

        return self::SUCCESS;
    }
}
