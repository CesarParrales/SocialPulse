<?php

namespace Modules\Ingestion\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Services\StoriesWatcherService;

class StoriesWatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(
        public readonly int $assetId,
    ) {
        $this->onQueue('ingestion-stories');
    }

    public function handle(StoriesWatcherService $service): void
    {
        $asset = ConnectedAsset::query()->findOrFail($this->assetId);

        $log = $service->watchAsset($asset);

        if ($log->status->value === 'error') {
            throw new \RuntimeException($log->error_message ?? 'Stories watcher failed.');
        }
    }
}
