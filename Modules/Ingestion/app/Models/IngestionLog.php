<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Enums\IngestionJobType;
use Modules\Ingestion\Enums\IngestionStatus;

class IngestionLog extends Model
{
    protected $fillable = [
        'asset_id',
        'job_type',
        'status',
        'records_ingested',
        'error_message',
        'executed_at',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'job_type' => IngestionJobType::class,
            'status' => IngestionStatus::class,
            'executed_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
