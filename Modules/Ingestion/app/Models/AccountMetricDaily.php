<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;

class AccountMetricDaily extends Model
{
    protected $table = 'account_metrics_daily';

    protected $fillable = [
        'asset_id',
        'date',
        'followers',
        'reach',
        'impressions',
        'profile_views',
        'posts_published',
        'engagement_rate',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'engagement_rate' => 'decimal:4',
            'captured_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
