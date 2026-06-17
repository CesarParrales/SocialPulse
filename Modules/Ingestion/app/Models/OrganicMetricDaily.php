<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;

class OrganicMetricDaily extends Model
{
    protected $table = 'organic_metrics_daily';

    protected $fillable = [
        'asset_id',
        'date',
        'metric_type',
        'value',
        'platform',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'value' => 'decimal:4',
            'captured_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
