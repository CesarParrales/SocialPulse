<?php

namespace Modules\Ingestion\Support;

use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;
use Modules\Ingestion\Models\IngestionLog;

class IngestionLogger
{
    public function log(
        ConnectedAsset $asset,
        IngestionJobType $jobType,
        IngestionStatus $status,
        int $recordsIngested,
        ?string $errorMessage,
        float $startedAt,
    ): IngestionLog {
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

        return IngestionLog::query()->create([
            'asset_id' => $asset->id,
            'job_type' => $jobType,
            'status' => $status,
            'records_ingested' => $recordsIngested,
            'error_message' => $errorMessage,
            'executed_at' => now(),
            'duration_ms' => $durationMs,
        ]);
    }
}
