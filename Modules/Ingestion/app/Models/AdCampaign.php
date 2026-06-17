<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Connections\Models\ConnectedAsset;

class AdCampaign extends Model
{
    protected $fillable = [
        'asset_id',
        'platform_campaign_id',
        'name',
        'status',
        'objective',
        'daily_budget',
        'lifetime_budget',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'daily_budget' => 'decimal:4',
            'lifetime_budget' => 'decimal:4',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AdMetricDaily::class, 'campaign_id');
    }
}
