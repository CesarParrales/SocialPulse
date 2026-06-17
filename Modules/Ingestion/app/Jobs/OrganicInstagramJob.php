<?php

namespace Modules\Ingestion\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Services\OrganicInstagramIngestionService;

class OrganicInstagramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $assetId,
    ) {
        $this->onQueue('ingestion-daily');
    }

    public function handle(OrganicInstagramIngestionService $service): void
    {
        $asset = ConnectedAsset::query()->findOrFail($this->assetId);

        $log = $service->ingestAsset($asset);

        if ($log->status->value === 'error') {
            throw new \RuntimeException($log->error_message ?? 'Organic Instagram ingestion failed.');
        }
    }
}
