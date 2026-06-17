<?php

namespace Modules\Ingestion\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Connections\Enums\AssetType;
use Modules\Connections\Models\ConnectedAsset;

class DispatchStoriesWatcherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('ingestion-stories');
    }

    public function handle(): void
    {
        ConnectedAsset::query()
            ->where('is_active', true)
            ->where('asset_type', AssetType::InstagramAccount)
            ->each(function (ConnectedAsset $asset): void {
                StoriesWatcherJob::dispatch($asset->id);
            });
    }
}
